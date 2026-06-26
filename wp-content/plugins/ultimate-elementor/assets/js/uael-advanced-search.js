/**
 * UAEL Advanced Search Widget JavaScript
 */

(function($) {
    'use strict';

    class UAELAdvancedSearch {
        constructor($scope) {
            this.$scope = $scope;
            this.$container = $scope.find('.uael-advanced-search-container');
            this.$input = this.$container.find('.uael-advanced-search-input');
            this.$button = this.$container.find('.uael-advanced-search-button');
            this.$results = this.$container.find('.uael-advanced-search-results');
            this.$resultsContent = this.$container.find('.uael-search-results-content');
            this.$resultsCount = this.$container.find('.uael-search-results-count');
            this.$loadMoreBtn = this.$container.find('.uael-load-more-button');
            this.$noResults = this.$container.find('.uael-search-no-results');
            this.$popularKeywords = this.$container.find('.uael-popular-keyword');

            this.settings = JSON.parse(this.$container.attr('data-settings') || '{}');
            this.searchTimeout = null;
            this.currentPage = 1;
            this.totalResults = 0;
            this.isLoading = false;
            this.currentSearchTerm = '';
            this.preventBlurClose = false; // Flag to prevent blur from closing results
            this.intersectionObserver = null; // Observer to track last result visibility

            this.init();
        }

        init() {
            this.bindEvents();
            // Hide load more button initially
            this.$loadMoreBtn.hide();
            // Setup intersection observer for load more button visibility
            this.setupIntersectionObserver();
        }

        bindEvents() {
            // Search input events with configurable debounce delay
            const debounceDelay = this.settings.search_debounce_delay || 300;
            this.$input.on('input', this.debounce(this.handleSearch.bind(this), debounceDelay));
            this.$input.on('focus', this.handleInputFocus.bind(this));
            this.$input.on('blur', this.handleInputBlur.bind(this));

            // Search button click
            this.$button.on('click', this.handleButtonClick.bind(this));

            // Popular keywords click
            this.$popularKeywords.on('click', this.handlePopularKeywordClick.bind(this));

            // Load more button
            this.$loadMoreBtn.on('click', this.handleLoadMore.bind(this));

            // Prevent clicks inside results from closing the container
            this.$results.on('click', function(e) {
                e.stopPropagation();
            });

            // Close results when clicking outside
            $(document).on('click', this.handleDocumentClick.bind(this));

            // Keyboard navigation
            this.$input.on('keydown', this.handleKeyboardNavigation.bind(this));

            // Prevent form submission on enter
            this.$container.find('.uael-advanced-search-form').on('submit', function(e) {
                e.preventDefault();
            });
        }

        handleSearch(e) {
            const searchTerm = e.target.value.trim();
            const minLength = this.settings.minimum_search_length || 2;
            
            if (searchTerm.length === 0) {
                this.hideResults();
                return;
            }

            if (searchTerm.length < minLength) {
                return; // Don't search until minimum length is reached
            }

            this.currentSearchTerm = searchTerm;
            this.currentPage = 1;
            this.performSearch(searchTerm, true);
        }

        handleInputFocus() {
            if (this.currentSearchTerm && this.$resultsContent.children().length > 0) {
                this.showResults();
            }
        }

        handleInputBlur() {
            // Delay hiding to allow clicks on results
            setTimeout(() => {
                // Don't hide if results are being hovered, if we're currently loading, or if blur close is prevented
                if (!this.$results.is(':hover') && !this.isLoading && !this.preventBlurClose) {
                    this.hideResults();
                }
            }, 250);
        }

        handleButtonClick(e) {
            e.preventDefault();
            const searchTerm = this.$input.val().trim();
            if (searchTerm) {
                this.currentSearchTerm = searchTerm;
                this.currentPage = 1;
                this.performSearch(searchTerm, true);
            }
        }

        handlePopularKeywordClick(e) {
            e.preventDefault();
            const keyword = $(e.target).data('keyword');
            this.$input.val(keyword);
            this.currentSearchTerm = keyword;
            this.currentPage = 1;

            // If results are already open, close them first for a smooth transition
            if (this.$results.is(':visible')) {
                this.hideResults();
                // Small delay to allow the close animation, then reopen with new results
                setTimeout(() => {
                    this.performSearch(keyword, true);
                }, 150);
            } else {
                this.performSearch(keyword, true);
            }
        }

        handleLoadMore(e) {
            e.preventDefault();
            e.stopPropagation(); // Prevent event from bubbling to document click handler
            if (!this.isLoading && this.currentSearchTerm) {
                this.preventBlurClose = true; // Prevent blur from closing results during load more
                // Store the current count of items before loading more
                this.itemCountBeforeLoadMore = this.$resultsContent.find('.uael-search-result-item').length;
                this.currentPage++;
                this.performSearch(this.currentSearchTerm, false);
            }
        }

        handleDocumentClick(e) {
            // Don't close if clicking inside the container or the results area
            if (!this.$container.is(e.target) &&
                this.$container.has(e.target).length === 0 &&
                !this.$results.is(e.target) &&
                this.$results.has(e.target).length === 0) {
                this.hideResults();
            }
        }

        handleKeyboardNavigation(e) {
            const $items = this.$resultsContent.find('.uael-search-result-item');
            let $active = $items.filter('.uael-active');

            switch (e.keyCode) {
                case 40: // Down arrow
                    e.preventDefault();
                    if ($active.length === 0) {
                        $items.first().addClass('uael-active');
                    } else {
                        $active.removeClass('uael-active');
                        const next = $active.next('.uael-search-result-item');
                        if (next.length > 0) {
                            next.addClass('uael-active');
                        } else {
                            $items.first().addClass('uael-active');
                        }
                    }
                    break;

                case 38: // Up arrow
                    e.preventDefault();
                    if ($active.length === 0) {
                        $items.last().addClass('uael-active');
                    } else {
                        $active.removeClass('uael-active');
                        const prev = $active.prev('.uael-search-result-item');
                        if (prev.length > 0) {
                            prev.addClass('uael-active');
                        } else {
                            $items.last().addClass('uael-active');
                        }
                    }
                    break;

                case 13: // Enter
                    e.preventDefault();
                    if ($active.length > 0) {
                        const link = $active.find('a').first();
                        if (link.length > 0) {
                            window.location.href = link.attr('href');
                        }
                    } else if (this.currentSearchTerm) {
                        this.currentPage = 1;
                        this.performSearch(this.currentSearchTerm, true);
                    }
                    break;

                case 27: // Escape
                    this.hideResults();
                    this.$input.blur();
                    break;
            }
        }

        performSearch(searchTerm, resetResults = false) {
            if (this.isLoading) {
                return;
            }
            // Require search term
            if (!searchTerm) {
                this.hideResults();
                return;
            }

            this.isLoading = true;
            this.showLoadingState();

            // Determine which action to use and calculate offset
            const isLoadMore = !resetResults && this.currentPage > 1;
            const initialResults = this.settings.initial_results || 5;
            const resultsPerPage = this.settings.results_per_page || 10;

            let offset = 0;
            let postsPerPage = initialResults;

            if (isLoadMore) {
                // For load more: offset = initial results + (page - 2) * results per page
                offset = initialResults + (this.currentPage - 2) * resultsPerPage;
                postsPerPage = resultsPerPage;
            }

            const data = {
                action: isLoadMore ? 'uael_advanced_search_load_more' : 'uael_advanced_search',
                search_term: searchTerm,
                posts_per_page: postsPerPage,
                offset: offset,
                nonce: this.settings.nonce,
                show_image: this.settings.show_image !== undefined ? this.settings.show_image : 'yes'
            };

            // Add post_types_json for initial search, post_types for load more
            if (isLoadMore) {
                data.post_types = JSON.stringify(this.settings.post_types || ['post']);
            } else {
                data.post_types_json = JSON.stringify(this.settings.post_types || ['post']);
            }

            // Enhanced search parameters
            if (this.settings.enable_taxonomy_search === 'yes' && this.settings.taxonomy_names && this.settings.taxonomy_names.length > 0) {
                data.search_taxonomies = 'yes';
                data.taxonomy_names = this.settings.taxonomy_names;
            }

            if (this.settings.search_ordering && this.settings.search_ordering !== 'relevance') {
                const orderMap = {
                    'date': { orderby: 'date', order: 'DESC' },
                    'date_asc': { orderby: 'date', order: 'ASC' },
                    'title': { orderby: 'title', order: 'ASC' },
                    'title_desc': { orderby: 'title', order: 'DESC' },
                    'comment_count': { orderby: 'comment_count', order: 'DESC' },
                    'menu_order': { orderby: 'menu_order', order: 'ASC' },
                    'modified': { orderby: 'modified', order: 'DESC' }
                };

                const orderConfig = orderMap[this.settings.search_ordering];
                if (orderConfig) {
                    data.orderby = orderConfig.orderby;
                    data.order = orderConfig.order;
                }
            }

            // Get AJAX URL with fallback
            const ajaxUrl = (typeof uael_advanced_search_script !== 'undefined' && uael_advanced_search_script.ajax_url) 
                ? uael_advanced_search_script.ajax_url 
                : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: data,
                success: (response) => {
                    this.handleSearchSuccess(response, resetResults);
                },
                error: (xhr, status, error) => {
                    this.handleSearchError(error);
                },
                complete: () => {
                    this.isLoading = false;
                    this.hideLoadingState();
                }
            });
        }

        handleSearchSuccess(response, resetResults) {
            if (response.success && response.data) {
                const data = response.data;

                if (resetResults) {
                    this.$resultsContent.empty();
                }

                if (data.posts_html && data.posts_html.length > 0) {
                    this.renderResults(data.posts_html);
                    this.updateResultsCount(data.total_count);
                    this.showResults();

                    // Handle load more button visibility based on whether there are more results
                    if (data.has_more && this.settings.show_pagination === 'yes') {
                        // Hide button initially, will be shown when last item is in view
                        this.$loadMoreBtn.hide();

                        // Use setTimeout to ensure DOM is updated before observing
                        setTimeout(() => {
                            this.observeLastResultItem();
                        }, 150);
                    } else {
                        // No more results, hide the button completely
                        this.$loadMoreBtn.hide();
                        if (this.intersectionObserver) {
                            this.intersectionObserver.disconnect();
                        }
                    }

                    // If this was a load more action, scroll to the first newly loaded item
                    if (!resetResults && typeof this.itemCountBeforeLoadMore !== 'undefined') {
                        this.scrollToNewlyLoadedItem(this.itemCountBeforeLoadMore);
                        this.itemCountBeforeLoadMore = undefined;
                    }

                    this.$noResults.hide();
                } else if (resetResults) {
                    this.showNoResults();
                }

                this.totalResults = data.total_count || 0;
            } else {
                if (resetResults) {
                    this.showNoResults();
                }
            }

            // Reset the preventBlurClose flag after search completes
            this.preventBlurClose = false;
        }

        handleSearchError(error) {
            console.error('Search error:', error);
            if (this.$resultsContent.children().length === 0) {
                this.showNoResults();
            }
            // Reset the preventBlurClose flag on error too
            this.preventBlurClose = false;
        }

        renderResults(postsHtml) {
            // Append server-rendered HTML
            this.$resultsContent.append(postsHtml);

            // Add click events to new items
            this.$resultsContent.find('.uael-search-result-item').off('click').on('click', function(e) {
                if (!$(e.target).is('a')) {
                    const link = $(this).find('a').first();
                    if (link.length > 0) {
                        window.location.href = link.attr('href');
                    }
                }
            });
        }

        updateResultsCount(count) {
            if (this.settings.show_total_results === 'yes' && this.$resultsCount.length > 0) {
                const currentlyShowing = this.$resultsContent.find('.uael-search-result-item').length;
                const text = this.settings.total_results_text || 'Total [count] Results';

                let countText;
                if (count > currentlyShowing) {
                    // Show "Showing 1-X of Z" format when there are more results
                    countText = `Showing 1-${currentlyShowing} of ${count} Results`;
                } else {
                    // Show standard format when all results are visible
                    countText = text.replace('[count]', count);
                }

                this.$resultsCount.html(countText).show();
            }
        }

        showResults() {
            this.$results.show();
            this.$container.addClass('uael-results-open');
            // Adjust height based on number of results
            this.adjustResultsContainerHeight();
        }

        adjustResultsContainerHeight() {
            const itemCount = this.$resultsContent.find('.uael-search-result-item').length;

            // Remove any existing count classes
            this.$results.removeClass('uael-results-few uael-results-many');

            // Add class based on item count
            // If 1-4 items, use auto height; 4+ items use fixed height with scroll
            if (itemCount <= 3) {
                this.$results.addClass('uael-results-few');
            } else {
                this.$results.addClass('uael-results-many');
            }
        }

        hideResults() {
            this.$results.hide();
            this.$container.removeClass('uael-results-open');
        }

        showNoResults() {
            this.$resultsContent.empty();
            this.$resultsCount.hide();
            this.$loadMoreBtn.hide();
            this.$noResults.show();
            this.showResults();
        }

        showLoadingState() {
            this.$container.addClass('uael-loading');
            this.$loadMoreBtn.prop('disabled', true);
        }

        hideLoadingState() {
            this.$container.removeClass('uael-loading');
            this.$loadMoreBtn.prop('disabled', false);
        }

        scrollToNewlyLoadedItem(previousItemCount) {
            // Get the first newly loaded item (the item at index = previousItemCount)
            const $allItems = this.$resultsContent.find('.uael-search-result-item');
            const $firstNewItem = $allItems.eq(previousItemCount);

            if ($firstNewItem.length > 0) {
                // Use setTimeout to ensure the DOM has updated with new content
                setTimeout(() => {
                    // Scroll smoothly to the first newly loaded item
                    $firstNewItem[0].scrollIntoView({
                        behavior: 'smooth',
                        block: 'start',
                        inline: 'nearest'
                    });
                }, 100);
            }
        }

        setupIntersectionObserver() {
            // Only setup if Intersection Observer is supported
            if (!window.IntersectionObserver) {
                // Fallback: show button immediately for older browsers
                this.useFallbackVisibility = true;
                return;
            }

            this.useFallbackVisibility = false;

            // Create observer that watches for the last result item becoming visible
            this.intersectionObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    // When the last item becomes visible, show the load more button
                    if (entry.isIntersecting) {
                        this.$loadMoreBtn.fadeIn(300);
                    }
                });
            }, {
                root: this.$resultsContent[0], // Watch within the scrollable results container
                rootMargin: '0px',
                threshold: 0.1 // Trigger when 10% of the last item is visible
            });
        }

        observeLastResultItem() {
            // Fallback for browsers without Intersection Observer support
            if (this.useFallbackVisibility) {
                this.$loadMoreBtn.show();
                return;
            }

            // Disconnect previous observation
            if (this.intersectionObserver) {
                this.intersectionObserver.disconnect();
            }

            // Find the last result item
            const $lastItem = this.$resultsContent.find('.uael-search-result-item:last');

            if ($lastItem.length > 0 && this.intersectionObserver) {
                // Start observing the last item
                this.intersectionObserver.observe($lastItem[0]);
            }
        }

        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    }

    // Initialize widget when Elementor loads it
    $(window).on('elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction('frontend/element_ready/uael-advanced-search.default', function($scope) {
            // Check if already initialized to prevent duplicate instances
            if (!$scope.data('uael-search-initialized')) {
                $scope.data('uael-search-initialized', true);
                new UAELAdvancedSearch($scope);
            }
        });
    });

    // Initialize for non-Elementor pages
    $(document).ready(function() {
        $('.uael-advanced-search-container').each(function() {
            const $widget = $(this).closest('.elementor-widget-uael-advanced-search');
            // Check if already initialized to prevent duplicate instances
            if (!$widget.data('uael-search-initialized')) {
                $widget.data('uael-search-initialized', true);
                new UAELAdvancedSearch($widget);
            }
        });
    });

})(jQuery);