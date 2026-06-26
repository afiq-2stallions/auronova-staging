/**
 * UAEL SVG Animator
 * 
 * @package UAEL
 */

(function($) {
    'use strict';

    /**
     * SVG Animator Class
     */
    class UAELSVGAnimator {
        constructor(element) {
            this.element = element;
            this.$element = $(element);
            this.isAnimated = false;
            this.isAnimating = false;
            this.isInViewport = false;
            this.observer = null;
            this.paths = [];
            this.loopTimeout = null;
            this.currentLoop = 0;
            
            this.init();
        }

        /**
         * Initialize the animator
         */
        init() {
            // Get settings from data attributes with new attribute names.
            this.settings = {
                animationType: this.$element.data('animation-type') || 'sync',
                animationTrigger: this.$element.data('animation-trigger') || 'viewport',
                animationDuration: this.$element.data('animation-duration') || 3,
                animationDelay: this.$element.data('animation-delay') || 0,
                pathTimingFunction: this.$element.data('path-timing-function') || 'ease-out',
                autoStart: this.$element.data('auto-start') || 'yes',
                replayOnClick: this.$element.data('replay-on-click') || 'no',
                looping: this.$element.data('looping') || 'none',
                loopCount: this.$element.data('loop-count') || 1,
                direction: this.$element.data('direction') || 'forward',
                fillMode: this.$element.data('fill-mode') || 'none',
                fillDuration: this.$element.data('fill-duration') || 1,
                staggerDelay: this.$element.data('stagger-delay') || 100,
                lazyLoad: this.$element.data('lazy-load') || 'no'
            };

            // Find SVG and paths.
            this.findSVGElements();

            // Setup animation trigger.
            this.setupAnimationTrigger();

            // Setup replay functionality.
            this.setupReplayClick();
        }

        /**
         * Find and prepare SVG elements for animation
         */
        findSVGElements() {
            const $svg = this.$element.find('svg');
            if ($svg.length === 0) return;

            // Find all animatable path elements.
            const pathSelectors = [
                'path',
                'circle', 
                'rect',
                'line',
                'polyline',
                'polygon',
                'ellipse'
            ];

            this.paths = [];
            pathSelectors.forEach(selector => {
                $svg.find(selector).each((index, element) => {
                    this.preparePath(element);
                });
            });
        }

        /**
         * Prepare a single path for animation
         */
        preparePath(pathElement) {
            const $path = $(pathElement);
            let pathLength = 0;

            try {
                // Calculate path length.
                if (pathElement.getTotalLength) {
                    pathLength = pathElement.getTotalLength();
                } else {
                    // For shapes without getTotalLength, estimate based on type.
                    pathLength = this.estimatePathLength(pathElement);
                }

                if (pathLength > 0) {
                    // Store path info.
                    const pathInfo = {
                        element: pathElement,
                        $element: $path,
                        length: pathLength
                    };

                    this.paths.push(pathInfo);

                    // Set initial dash properties
                    $path.css({
                        'stroke-dasharray': pathLength + ' ' + pathLength,
                        'stroke-dashoffset': pathLength
                    });

                    // Handle fill mode settings.
                    this.setupPathFillMode($path);

                    // Add animation class.
                    $path.addClass('uael-svg-animate');
                }
            } catch (error) {
                console.warn('UAEL SVG Animator: Could not measure path length', error);
            }
        }

        /**
         * Setup fill mode for a path
         */
        setupPathFillMode($path) {
            switch (this.settings.fillMode) {
                case 'before':
                    // Fill is visible before animation.
                    $path.css('fill-opacity', '1');
                    break;
                case 'after':
                    // Fill appears after animation.
                    $path.css('fill-opacity', '0');
                    break;
                case 'always':
                    // Fill is always visible
                    $path.css('fill-opacity', '1');
                    break;
                case 'none':
                default:
                    // No fill.
                    $path.css('fill-opacity', '0');
                    break;
            }
        }

        /**
         * Estimate path length for shapes without getTotalLength
         */
        estimatePathLength(element) {
            const tagName = element.tagName.toLowerCase();
            let length = 0;

            switch (tagName) {
                case 'circle':
                    const r = parseFloat(element.getAttribute('r')) || 0;
                    length = 2 * Math.PI * r;
                    break;
                    
                case 'ellipse':
                    const rx = parseFloat(element.getAttribute('rx')) || 0;
                    const ry = parseFloat(element.getAttribute('ry')) || 0;
                    // Approximation for ellipse circumference.
                    length = Math.PI * (3 * (rx + ry) - Math.sqrt((3 * rx + ry) * (rx + 3 * ry)));
                    break;
                    
                case 'rect':
                    const width = parseFloat(element.getAttribute('width')) || 0;
                    const height = parseFloat(element.getAttribute('height')) || 0;
                    length = 2 * (width + height);
                    break;
                    
                case 'line':
                    const x1 = parseFloat(element.getAttribute('x1')) || 0;
                    const y1 = parseFloat(element.getAttribute('y1')) || 0;
                    const x2 = parseFloat(element.getAttribute('x2')) || 0;
                    const y2 = parseFloat(element.getAttribute('y2')) || 0;
                    length = Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y2 - y1, 2));
                    break;
                    
                case 'polyline':
                case 'polygon':
                    const points = element.getAttribute('points');
                    if (points) {
                        length = this.calculatePolylineLength(points);
                    }
                    break;
            }

            return length;
        }

        /**
         * Calculate polyline/polygon length from points
         */
        calculatePolylineLength(pointsStr) {
            const points = pointsStr.trim().split(/[\s,]+/);
            let totalLength = 0;

            for (let i = 0; i < points.length - 2; i += 2) {
                const x1 = parseFloat(points[i]);
                const y1 = parseFloat(points[i + 1]);
                const x2 = parseFloat(points[i + 2]);
                const y2 = parseFloat(points[i + 3]);

                if (!isNaN(x1) && !isNaN(y1) && !isNaN(x2) && !isNaN(y2)) {
                    totalLength += Math.sqrt(Math.pow(x2 - x1, 2) + Math.pow(y2 - y1, 2));
                }
            }

            return totalLength;
        }

        /**
         * Setup animation trigger
         */
        setupAnimationTrigger() {
            // Always setup viewport observer for looping functionality.
            this.setupViewportObserver();
            
            // Check auto start setting first.
            if (this.settings.autoStart === 'no') {
                return;
            }
            
            switch (this.settings.animationTrigger) {
                case 'auto':
                    // Start animation immediately with optional delay.
                    if (this.settings.animationDelay > 0) {
                        setTimeout(() => {
                            this.startAnimation();
                        }, this.settings.animationDelay * 1000);
                    } else {
                        this.startAnimation();
                    }
                    break;
                    
                case 'viewport':
                    // Viewport observer already setup above.
                    break;
                    
                case 'hover':
                    // Setup hover trigger.
                    this.setupHoverTrigger();
                    break;
                    
                case 'click':
                    // Setup click trigger.
                    this.setupClickTrigger();
                    break;
                    
                case 'delay':
                    // Start after specified delay.
                    setTimeout(() => {
                        this.startAnimation();
                    }, this.settings.animationDelay * 1000);
                    break;
                    
                default:
                    // Default to viewport trigger (already setup above).
                    break;
            }
        }

        /**
         * Setup viewport intersection observer
         */
        setupViewportObserver() {
            // Check if IntersectionObserver is supported
            if (!window.IntersectionObserver) {
                // Fallback: start animation immediately
                this.startAnimation();
                return;
            }

            const options = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1 // Trigger when 10% of element is visible
            };

            this.observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.$element.addClass('uael-svg-in-view');
                        this.isInViewport = true;
                        
                        // Start initial animation if not animated yet and viewport trigger.
                        if (!this.isAnimated && !this.isAnimating && this.settings.animationTrigger === 'viewport') {
                            setTimeout(() => {
                                this.startAnimation();
                            }, 100); // Small delay for CSS transition
                        }
                    } else {
                        this.$element.removeClass('uael-svg-in-view');
                        this.isInViewport = false;
                        
                        // Clear any pending loop timeouts when out of viewport.
                        if (this.loopTimeout) {
                            clearTimeout(this.loopTimeout);
                            this.loopTimeout = null;
                        }
                    }
                });
            }, options);

            this.observer.observe(this.element);
        }

        /**
         * Setup hover trigger
         */
        setupHoverTrigger() {
            this.$element.find('.uael-svg-container').on('mouseenter', () => {
                if (!this.isAnimated && !this.isAnimating) {
                    this.startAnimation();
                }
            });
        }

        /**
         * Setup click trigger
         */
        setupClickTrigger() {
            this.$element.find('.uael-svg-container').on('click', (e) => {
                // Only prevent default if we're not inside a link.
                if (!this.$element.closest('a').length) {
                    e.preventDefault();
                }
                if (!this.isAnimated && !this.isAnimating) {
                    this.startAnimation();
                } else if (this.settings.replayOnClick === 'yes') {
                    this.replayAnimation();
                }
            });
        }

        /**
         * Setup replay click functionality
         */
        setupReplayClick() {
            // Check if replay on click is enabled and not already handled by click trigger.
            const replayEnabled = this.settings.replayOnClick === 'yes' || this.settings.replayOnClick === true;
            if (!replayEnabled || this.settings.animationTrigger === 'click') return;

            this.$element.find('.uael-svg-container').on('click', (e) => {
                // Only prevent default if we're not inside a link.
                if (!this.$element.closest('a').length) {
                    e.preventDefault();
                }
                this.replayAnimation();
            });
        }

        /**
         * Start the animation based on animation type
         */
        startAnimation() {
            if (this.isAnimating || this.paths.length === 0) return;
            
            this.isAnimating = true;
            this.$element.addClass('uael-svg-animating');

            // Handle direction by reversing path order if needed.
            if (this.settings.direction === 'backward') {
                this.paths = this.paths.reverse();
            }

            switch (this.settings.animationType) {
                case 'sync':
                    this.animateSync();
                    break;
                case 'delayed':
                    this.animateDelayed();
                    break;
                case 'one-by-one':
                    this.animateOneByOne();
                    break;
                default:
                    this.animateSync();
            }
        }

        /**
         * Animate all paths simultaneously
         */
        animateSync() {
            const duration = this.settings.animationDuration * 1000;
            let completedPaths = 0;

            this.paths.forEach(pathInfo => {
                this.animatePath(pathInfo, 0, duration, () => {
                    completedPaths++;
                    if (completedPaths === this.paths.length) {
                        this.onAnimationComplete();
                    }
                });
            });
        }

        /**
         * Animate paths with staggered delays
         */
        animateDelayed() {
            const duration = this.settings.animationDuration * 1000;
            const staggerDelay = this.settings.staggerDelay;
            let completedPaths = 0;

            this.paths.forEach((pathInfo, index) => {
                const delay = index * staggerDelay;
                this.animatePath(pathInfo, delay, duration, () => {
                    completedPaths++;
                    if (completedPaths === this.paths.length) {
                        this.onAnimationComplete();
                    }
                });
            });
        }

        /**
         * Animate paths one by one (sequential)
         */
        animateOneByOne() {
            const duration = this.settings.animationDuration * 1000;
            const pathDuration = duration / this.paths.length;
            let currentIndex = 0;

            const animateNext = () => {
                if (currentIndex >= this.paths.length) {
                    this.onAnimationComplete();
                    return;
                }

                const pathInfo = this.paths[currentIndex];
                this.animatePath(pathInfo, 0, pathDuration, () => {
                    currentIndex++;
                    animateNext();
                });
            };

            animateNext();
        }

        /**
         * Animate a single path
         */
        animatePath(pathInfo, delay, duration, onComplete) {
            setTimeout(() => {
                const $path = pathInfo.$element;
                const timingFunction = this.settings.pathTimingFunction;

                // Create CSS transition.
                $path.css({
                    'transition': `stroke-dashoffset ${duration}ms ${timingFunction}`,
                    'stroke-dashoffset': '0'
                });

                // Handle completion.
                const handleComplete = () => {
                    $path.off('transitionend', handleComplete);
                    if (onComplete) onComplete();
                };

                $path.on('transitionend', handleComplete);

                // Fallback timeout in case transitionend doesn't fire.
                setTimeout(handleComplete, duration + 50);

            }, delay);
        }

        /**
         * Handle animation completion
         */
        onAnimationComplete() {
            this.isAnimating = false;
            this.isAnimated = true;
            this.$element.removeClass('uael-svg-animating').addClass('uael-svg-animated');

            // Clean up CSS transitions
            this.paths.forEach(pathInfo => {
                pathInfo.$element.css('transition', '');
            });

            // Handle fill animation if enabled.
            this.handleFillAnimation();

            // Handle looping
            this.handleLooping();
        }

        /**
         * Handle fill animation
         */
        handleFillAnimation() {
            if (this.settings.fillMode === 'after' || this.settings.fillMode === 'always') {
                const fillDuration = this.settings.fillDuration * 1000;
                
                this.paths.forEach(pathInfo => {
                    const $path = pathInfo.$element;
                    $path.css({
                        'transition': `fill ${fillDuration}ms ease`,
                        'fill-opacity': '1'
                    });
                });
            }
        }

        /**
         * Handle animation looping
         */
        handleLooping() {
            // Only loop if the element is in the viewport.
            if (!this.isInViewport) {
                return;
            }
            
            if (this.settings.looping === 'infinite') {
                // Loop forever when in viewport
                this.scheduleNextLoop();
            } else if (this.settings.looping === 'count') {
                // Loop specific number of times
                this.currentLoop = (this.currentLoop || 1) + 1;
                if (this.currentLoop < this.settings.loopCount) {
                    this.scheduleNextLoop();
                } else {
                    // Reset loop counter
                    this.currentLoop = 0;
                }
            }
        }
        
        /**
         * Schedule the next loop iteration
         */
        scheduleNextLoop() {
            this.loopTimeout = setTimeout(() => {
                // Check again if still in viewport before looping
                if (this.isInViewport && !this.isAnimating) {
                    this.replayAnimation();
                }
            }, 500); // Small pause between loops
        }

        /**
         * Replay the animation
         */
        replayAnimation() {
            if (this.isAnimating) return;

            // Reset animation state
            this.isAnimated = false;
            this.$element.removeClass('uael-svg-animated');

            // Reset path dash offsets
            this.paths.forEach(pathInfo => {
                pathInfo.$element.css({
                    'stroke-dashoffset': pathInfo.length,
                    'transition': ''
                });
            });

            // Start animation after a brief delay
            setTimeout(() => {
                this.startAnimation();
            }, 100);
        }

        /**
         * Destroy the animator
         */
        destroy() {
            if (this.observer) {
                this.observer.disconnect();
                this.observer = null;
            }
            
            // Clear any pending loop timeouts
            if (this.loopTimeout) {
                clearTimeout(this.loopTimeout);
                this.loopTimeout = null;
            }

            this.$element.find('.uael-svg-container').off('click');
            this.$element.removeClass('uael-svg-animating uael-svg-animated uael-svg-in-view');

            // Reset path styles
            this.paths.forEach(pathInfo => {
                pathInfo.$element.removeClass('uael-svg-animate').css({
                    'stroke-dasharray': '',
                    'stroke-dashoffset': '',
                    'transition': ''
                });
            });

            this.paths = [];
        }
    }

    /**
     * Initialize SVG Animators
     */
    function initSVGAnimators() {
        $('.uael-svg-animator').each(function() {
            if (!$(this).data('uael-svg-animator')) {
                const animator = new UAELSVGAnimator(this);
                $(this).data('uael-svg-animator', animator);
            }
        });
    }

    /**
     * jQuery Plugin
     */
    $.fn.uaelSVGAnimator = function() {
        return this.each(function() {
            if (!$(this).data('uael-svg-animator')) {
                const animator = new UAELSVGAnimator(this);
                $(this).data('uael-svg-animator', animator);
            }
        });
    };

    /**
     * Document Ready
     */
    $(function() {
        initSVGAnimators();
    });

    /**
     * Elementor Frontend Init
     */
    $(window).on('elementor/frontend/init', function() {
        if (typeof elementorFrontend !== 'undefined') {
            elementorFrontend.hooks.addAction(
                'frontend/element_ready/uael-svg-animator.default',
                function($scope) {
                    $scope.find('.uael-svg-animator').uaelSVGAnimator();
                }
            );
        }
    });

    /**
     * Handle dynamic content (AJAX/partial loads)
     * Re-initialize for newly added elements dynamically.
     */
    let svgAnimatorTimeout;
    $(document).on('DOMNodeInserted', '.uael-svg-animator', function() {
        clearTimeout(svgAnimatorTimeout);
        svgAnimatorTimeout = setTimeout(initSVGAnimators, 150);
    });

    /**
     * Expose class globally for advanced usage
     */
    window.UAELSVGAnimator = UAELSVGAnimator;

})(jQuery);