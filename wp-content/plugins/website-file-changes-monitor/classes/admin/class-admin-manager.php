<?php
/**
 * Handle emails.
 *
 * @package MFM
 * @since 2.0.0
 */

declare(strict_types=1);

namespace MFM\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use MFM\Helpers\Directory_And_File_Helpers;
use MFM\Helpers\Settings_Helper;
use MFM\Helpers\Events_Helper;
use MFM\DB_Handler;
use MFM\Helpers\Setting_Validator;
use MFM\Crons\Cron_Handler;

/**
 * Handles admin areas within the plugin.
 *
 * @since 2.0.0
 */
class Admin_Manager {

	/**
	 * Current setting errors.
	 *
	 * @var array
	 *
	 * @since 2.0.0
	 */
	public static $settings_validation_errors = array();

	/**
	 * Icon for use in dashboard (datauri).
	 *
	 * @var string
	 *
	 * @since 2.0.0
	 */
	public static $icon = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iMTI4IiBoZWlnaHQ9IjEyOCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSIgdmVyc2lvbj0iMS4xIiB2aWV3Qm94PSIwIDAgMTI4IDEyOCI+CiAgICA8aW1hZ2Ugd2lkdGg9IjEyOCIgaGVpZ2h0PSIxMjgiIHhsaW5rOmhyZWY9ImRhdGE6aW1hZ2UvcG5nO2Jhc2U2NCxpVkJPUncwS0dnb0FBQUFOU1VoRVVnQUFBSUFBQUFDQUNBWUFBQUREUG1ITEFBQUFBWE5TUjBJQXJzNGM2UUFBSUFCSlJFRlVlRjd0dldtUVhjZDFKbmd5NzM3djIvZFg5V3BGb2JDVEJNRkYzRVJRRzBXSzFHcEF0anl0MWpKRDlvekhudmIwSnZkRURJdmhHRS9MNDdDbjFkRWRUVTNZa2hWdXl3MW9NU1dLSWlsUkJHUXVFb2tpQ0JCVldBcW92ZXJWMjlmNzdwNDVrYTlRRkVTQ1JMMENRSUJFM1FqZ1Q5MTNiK2JKTDArZWM3NXp6a1d3ZmwzVEVrRFg5T3pYSncvckFMakdRYkFPZ0hVQVhPTVN1TWFudjY0QjFnRndqVXZnR3AvK3VnWllCOEExTG9GcmZQcnJHbUFkQU5lNEJLN3g2YTlyZ0hVQVhPTVN1TWFudjY0QjFnRndqVXZnR3AvK3VnWllCOEExTG9GcmZQcnJHbUFkQU5lV0JDaWxhUDkrd1BFNElOaDlkdTRIQUFvRm9IdjJBRUVJMFd0Skl0ZWNCdGozWE42SEpXMWozTS9GRTBFT2VCNmdWUE9nM1BRSzRKbG43djlBdE01QXdrQndMWURobWdEQWM4OVJYc21BbWdpQWtpOFpBNGFOL3JsUGhsM1JBSTg0REZEVkNXb2EzcThsQVgwM0ZKUlBUUmQwVk04UnQ4YjU5Uy9mZzh6M3MwWjQzd0xnM0YzODRzbFd0OEtoajJnS3R5dWs0SVJENEZZT1E0OHNJSVF3Z0cxVElBQXpQQWVIR2pya3BnczJ6VlhjaFlaQm53dlVBNlB2NTZQaGZRbUE3ejZqZHdVRGRIdFhWQWgxeFRraThLalhzc2k5Q0tHYmZBcm1CUjRVaEVEQWFIbjZsRktnQUk1SHdHaTBpRnRwZU5DeTZBSkc4Q3pHNk5kelphZFNMS0NqLyt6ajZoTEErOHRHZU44QTRMbm5xQnpzTTVPcEFGYnlkWFN6NjVEUEJUVGNHdzl5UkpPUlJpbWtLRUNJNXhBd3RYKytpMUFBMTZYZ3VCUThBcGJyMFZ6ZEpNVlNsU3lLUFBxSGVBZ096K2FwTzdWb2VsTUdsRjl0QmV2Nzl5THZ2WHhFdkNjQndOVDdnUVBBU1JJSVBUMEFrZ1Jjc1c3M3V4Zytrd2h4R1lHSElkZUZYUUtQZ29xRVFCUVE0RFhNbEFHaFpWR3diRm9YT1hqWjltQnFxZUo2Y3puU2JKbk9nYUNFWHhsTStHcno4K0R0M2czZWU5Rm9YSU5Zcmp6ZUh6dEVoUzY3c2NHdjhEdFNNVTVLaGptTUtCcldMZnA1VFVZWlZVWThoNEZEZ0JCTGZMK1lTVkxhUGlJb0llQzJiRXBxVFVKcVRhK0pNZm9aNHRBTHBacWJMZW5leWRjTTM2bVJlNUI3NWFYVDJRZ3VSamFkdmVraTduN29NU3BzaWJWaVBXa3YyQitWSUJKQWdVYVRmcGdBL1Z3a3dBV2lBUXl5aUJWQ2FBSmhFRG5jWHZkTGVqRWdFRXJCOHdCY0R6eVAwRXJkb00xU3pjbFRELzg0RWtGUFZ4dTBNWk8xWUxyQTFmL3c3OVFjN0wvNmo0ZExMYWRMSjNSSzBZOUhRZkZ4VmRFaVNxelZkTzcxYVhobmQ0eVhreEZPNGhBYThqeTZUUlFSejZ4NWdYOTNwK0tTOXRFQXBrMHREcU9UUU5HcFFzMjFGa3FPV2E2VEk3RWcvMFFnWUZZbXNpRnJ6MjFnWHEzSHc3c3J0WGVBeDFzaWRPTWdHN3E1S3hhQ2dZaktaMHpidTVmRDZJYUFocFdnRDRNaUluYXRHUEtYRG5nZFBvbUZEU2tCWWpxRTFuVUtkZDB6SEE5ZUR5ajRpWnJ1THBWMU9DMHA4bXUzRGtIcndGVVljYnhxQVBDZHA1YzBrT1ZFVDRqVCtpSWlGbVFjS1pUSmwyUUo3bzRGT2IrbVlKWG5RTVpzMVRHc3lhanJjRzA3dXAwZEVSNDdKa2piWGpCTm0rckZtbWVZRHYxNTNJLy9UbmRKWVQ3dlFMbmh0c3JVeVgvMXpuaWpveGRjcHB1dktBRDI3YU9jYnlmd0FRL0VjcW0xbVZDNkp4cmd0bWNTbUl2Nk9Ka2lOSVFCNG9LQWVLYmlMOFBSZmxuRXlzSUtMZ0d3SGVvaGdCeEdNSm12ZWVaOHdjWGxoamZHSTI1L1Y3OTh1R3lBVjNnRm5MMVgwSlY4VndIQTFQeWpqd0xhdWhYUW5qMUFmL3k4bVpGVWVsdlN6MjBNK1ZDdjY4RUhCUUgxK2xXTU5Ca2hua004UXNBdHgrVXZ5MXBkdG9lMkdhVmxXc2xqSGtUVElMVGU4cERqd3B6QTQrZDFpMDdrS3M2VTQ2QVhQckpMWHRnUEFPTUE5QkVBK203YUMrK2FXRWRHS0U1ZTE0eUZ3cmkzSjRLVndUUlBMWmR1MHkzNkdVVkNPeU1CTE1nQzluRVlCSXlYL2ZiMzJxSy9IWm9ZRGdocEh3OUFLRGlPQzNxMTZaa3RreDVURmRqUGNlajQxSktMNW5LMjNhaHpVMEZMSzc1Yld1R3lBcUJ0MkkyREVMZXJhaXdvU0xONWZDZEM4SmxrR0dlNm9qenlLU2dFQUwwWVFZZ0ZhemptdVYrMlBYbjFQTmhqeDROTGdWS29JUVF6VFpQVTVnc3VGQ3BlQ1ZQMDNlNEUvYWVXcDdZVUU0eXRXOEc1bkJyaHNzcWJzWEIycExsWnc5eDltWmlRNERuWTRucjBaa25FRVorQ2tDd2l4REdyN3RwWTkvTWhrQm1NMUxBSmJiWUljeWwxbmtjdnVCNmN5TmU5UmNORVQ1T1NlUHlleXhoZ3VxUUFZQUdibXpLTmdYaVEyOUNiNExoTW5CZGRRcTh6VExvM3FPR1lKbU9adzZCaXpOWWRnREZ4bDNRQVY4OG03MmdrWjQ4R2RreFFqMEJUTjRtbEd6U25pUEFERHFQWHNtWFhYaWk1YnJaQ3B2LzJKZi9wQXlPWEx1SjRTZVQvdytjcW9YUkc4c25BeDJ1Nit6R0U0V09KSUtla0loeFNaQnh4WERyQWMwZzRhOGwzSkp4cjdXWm1LN2dlQlplQUxXQ1lzVnhTS3RRSXpWZUpicnYwdVlqRy9ZUVN0MXhzV0kxN2RvYXJGeXVmamdIQXp2VnZqZ0kvM0FBdWt3SHFlU0RtN05aZEFRWGZFZzBLR1FUMEJrcnA5YXFFZVUxaElkcU9YM0d4YzNwZi9kNTJLT2dXQmNNaU5sQVlBMENIS3JxM3BMZm9yd2ZqMGkrYlRYQ091MERuYWtBZTJnVnVwL1pDeDZ2RFVxbzR3YmM5cEVBNmsrQWdGZWFVU3BQY0Q0QStIRkJ4UkZNUTRuSGJmV05uKy9wMUNTU3dURWl4NDRGNkxaTjZUWU5VS2FYUEJnTDRKOFVLbUhONW0xWWJzRmdxNitOZi9WUm5BYVpWTFJFejVzRGZDQ1ZDWXNqanVPNWF3LzZpSkhLNzRpSE1KMEk4NWppSUFRWEd5VEVXN24zanZsMkN0YnVrajJoSEd3bjdSMTFBVUtFRWl1VzZSNWFxSGpFdCt1dVFENzRyWWpTVHRaeTZXdk5WYjdvSk9SY2F3SGtCTVBJYzVYZjVRUXo3Z0J1T0FLMGFWcUpwMDkycWlHNkxCUGl3UjhndEdPRzBMQ0ZPbFRCd1hOdVlXeFdZTGpTZzliK3ZUZ0pNS1RCbTByUUpHQlpsdVN6eklvOWZyZW1rMkRESnIyVk1EZ1JsS1hlcURLalNCTS9YQVB0ODNzUWJpOWFPMGdHZ1J3RFE0NzgwdWtTUmJvOEVjYXczSlJCVlJHbmRJQjlIQ080TWFCakxBdVl3aG5aZXpicWFYOTJDWGE2N21GWm9CeDBwRU1lalhyMUZDQ0h3b2lxaW4xb2VYVmpJRXk1ZmRVcXVoNDRxdHlwTHU2R2QrazVXeHZNR0FQYTlTSld3YU1hN2swaHFOZW50Tm9VditHVzBOUkhoU1VqREFvZFFnQUpvVnlNUmM3bUUrMTU3Ymp0bmdRQUZCQzJQUUVOdkVhZFE5NkNtazFNU1Q3L2prOUd2NWl2RXVIdUhPdmNXQUR6K2ZIMlRMSEwvUTArQ0g0b0d1UjRPd1dhZVF4RlpSTFR0dnVHMmlsOVg4KzhOVkxUelhGbEttK2xRNUhxMGhnQk9WdXAwZnE1b2orKytRZnMvM3dLQTc3OWc5R2s4dWE4dnlmZEVneHhiL0ErSUFuUXhBTEJvM1h0ajN1dWpQRmNDaEFLMUhBcU5sa2NhT21uVVd2Um9YU2MvdUdlbitoL2ZBb0FubjZRU2plbmgvaWdueWdxK2lVUG8wejRWYjFZbEZLYUF1amtNeW5vZzUrb0hHRHNHSEk4eTRxbUZFTXBhTmlrdWxEMjZWQ1RWUnN2N2hTeHczN3YzVm1YcUxRQTRkMnFIVHRLWXJEZ0RVUitPdVI2OXhYSHBaelVaRHdaVWpOdEpHUmkxazJ6WDljTFZBWWl6aWF0dFE1QlFNQnN0UWhvR25lUTVlRndSNGNYSm5PZk5aaDByMS9EbVk2M2d6TGxNNDNsVis5bXFHalE2Q3B3dDJwdGxoZDRaOW5GOWlnU2JIQmMrN0ZleFg1VVJzQno3OWJQaHlvT0FoWTUxZzBMTElEVkJRQWRzaHg0dk5PaE13NENYN3R3bWpMRlVOSFlkUEFoa1pPUTNIa0RiaTd2UThGbkJoWnBxYUFGWjhOY052Sk5pOHZudUNKOVFaY2dnd1AyaWdBUkpnR3VHeXIyUXZONnR2N09rVkJZbXRoMVcxUVpuR2daWnlGWGNMQkQ4ZlUwaWh4cVcwMnd0K2ZWN0xsRGJlRUVBdkRHaEVZci81a1BOYUsrUEc4ckVjZEEwWWJkSDRSUHhJQmVLQkRDVlJhUUFvQUFDRUptcnVINWRlZ213YUkvbnRhdVdpTzFSdmE2VGVxMUpHaHlHSDNLVWUyRythQmVuODk3a1YrNzNGVmRid3JaNkFKd3puNUVSeXZmY1hyc2hyb20zRDNSei9wNDRUM21BRFM2QnV3UUI5Yk9NWFFEZ29GMlpzWDVkakFSWW5BZXh4R01LbnNVS1UzUVA2U1p0RWc5R0hZKyt1RlQyS3JwTmYybk9xY2YyN2tWMnArOWEwL3FzTUlMOUFvZ1p2cDNqUjA4dnVOY2hSRDRiOG5FN2ZES0tlUVEyWWc0RnhPVmt6dlZyRFJKZ08vNXNuV0tkUTNDbVpaSGNmTUhEMmJKVE1VMzBUSGZDKzFISjlCdFJCK3hkYTJBQ1YyVURySGJjejUrZy9yQm1KUk4rSE5STjJPbTQ4SHNCRlc4SytiRE1pakk1akxoMW91akMwbnlqQW9rd3pnZHFUWU9ZdWtGTzhBajJTeEs4UExNSU1GKzFuVW9GQ2pQM2EvbVJjOEs2RjM3NlcrKzRaSHR6eFhOZ3IvajVxSmtKK2ZBdGlSREtTQUxlYk5qMFFiK0tnMzRGQ1J5SGhEWlZ2TzVHdnJFYTdlS1MzMUMram00U3QyblFzc1NqbnpvZU9WN1M2YXhwa2xkdjJTUlBNN0U5Q2dDWEtudjRrZ0hnWEd5TlVJcjNqQU52eUtBMEd1WU9IdFBmemNTRnRDYmpmZ0E2TEl0SWxVVU1QR01SbHozSnl6S090ZXlJZC9zM2JPRmRqMFhzQ0xWdGFnRkM0dzJEek9VcXpyeEQwRDVabG8rZVRRNWx5UjV2a0RpWGFweVhWZkJNS3p4ekZGVHNtTkhlS0ZiemRlOWpCTkR2cGFOY0tobmlRSld4aEJDRUVJQnlMWGtPNTZoNXR2aXVhWk5HclVucXhicFhRQWo5YlVoR3Y2alV2WWFPbGNydXJhQjNtdVhUQ1RndUt3RFlRRlpvNWdkR2dUdGRxL1dxaXJCbElNMXJQVEdPVUVDYkhaZCtVaExROVQ0RnNmaGlPMWUwa3dtOEIrOWxtY0NlYmhKYXFubTQzaUpGU3RIUE9BNmV6amRvemJCaGJFQ1Y1cmR1QlZacWZ0bUxSTjVWWVQvMkdCV1VmaEJ2NlFNYzJnU2tPV2NQRUI1OUtLamlyWnFJdXgyWDNzenprRkFrbGpiOEhsemFkeGh5TzBicnRQMzRKWUZEbzdwSlptZUxMczZWU2FWbDB1Zjd1dFhuUzAxd3hUSllsek1OL00xRGZGY0I4T2FYUHpsQnBUNWVEd2MwemVjNDFsYkx3YjhiOUtPdElRMzdDSVVlbmtQaWU1bUFXc253WmUxbUVJS0Zwa0diaHVVZFF4eit2c2h6cjUyZXM5QlNucnJGa2xGOWVHK2tkaVVnM3pFQVdJbFgzOTBnYWdCOFZ4cVE0d0ZOQU5nWFc4SHkxSXUxU0RBaWJFa0h1QlRINFcxTmszd2g3T2U2QWhvV0JCNUV6RXJCM3dPZVE5dWFaNlZnbEJMWEJidGhFRmMzNklJczR1OTZRTWVhVFpwdG1zYkptellGaW10ZDhEY3FyZ0JFZ1Z0ZUE4aURlM0EzMkoyNmhSMEQ0RC9zS3dmalFlNzJaRUFZQ3ZsQVFvQnRtNUNYbTQ1eTlNR2JVT3VpSmdXQTQ5TWcwRnB6QTZiYy9aa2tuNVFGdEpWU3VFMlJjRWlURVVqQzFSdGVaQXZmN2l0a3N0NUNwTUVoOUUrV1EwOFY2dDRDY2RCUDQ2cDRwcjhmV0tMbVJYVWtiV2R2U2NiMUFvOXZVVVVRNndhaFRaMmVLdXJ1Qy8vang0UGxUdFpnVlFENHhwTlVHa3BZbVlTZnBuMHlUbFVhNUhPaWlIWnBFbUpCSGtzVTBFOWtBVDlyT2lTYnJUdUxkMjVXczZ1TlJiOWxzSlNpYi93VXhBMHhQVHdZNStTS1RtK2pnRDhUQytMdW9JYVRJZy9kQW85a0JvUTJHN21xR1hRaWtzN3VaWXZPa2pOWjRvWGpFdHZ6WUxIWklvdjVHbGtDalBhRlpQcEt0ZW0yOUx4V3ZSQXg4NDV2SHFINHFRZGF5V1JZeUVRMG5MUnM4bUdIMEFjRURrbUdUWW5uMGRHQWh2WmJMcDVmeUR0MHBrZ2F4Ykk3OXllL0g2cTgwM1BQS3o2MjRKbG93OWZyNThTZVhwVlVxMDVLTitqSEZSSHVTZ1E1UHdFWXhnZ1NITWNTUTFuR0VFeTVIcHB0dE54Rng0Vm5GVlY0MXFxQ05WN1FJYi9nbUROSFF2VTMwNUNyRmZPM2Z0Sk1oWVBjeHA0a0NsR1gzbzRRK2xoQVExM3hFQSthZ2dVQUdrQUlDY3lOZkxld2NEWm93MVE5aTcwM1d5YXhpalVQMTNSU0FJQ2Y4U0w4WWk1SEc5VzZkK0xVODFwaExYTm5SMjE2YThXZjZoWGx3UVNBWCtYVVpvdTdpK1Bodm5nSUp5VWU5WG9lRExJNENrc1ZwK3dRQUhxcTJ2SWErVEtoMVNZNXcyRjRxanRHWDgzbWlWUDIrUnQ3dDcyVksvanRyT0FEQjdnSC9INVVndUVOUENkOGdPMjY3Z2hQUkJIaUxZUGVoUkRzWXJYN2JQZWQ2N2N6Z2JBZFlOcTBpUkY2Q1hQMHBVS0ZXSE41eDh2VnZUT0tTZytrd1YrYm5BU3kxcTZiVENDSm0ybzc0ejd1OXE0NG54eEk4eWdnNFM3VHBYZnhQT3JWbGoySHkwNUFzVnc3NXNZWk5xVWVwVE1paHBkYUZwbWR6Ym5jZk1rcmxYVHYrUysvRUhnWjNzUzdyd2J3SzIxeUJnY0JuMm8wZ2tEZ3RsaVEzOWdYRjhSRUJLdUVvcHM5ajl5bEtVaFRSUHhiMm84Wm5LemlXRGNKTkEzQ3FPSTVqa1BQZXk0ZHk5VkkxaUhvaFNSL1ppcWZ6NU43N3JubmpXNW1id0RnUHp6MnMyQXdvQTNldWkzalZ3T3hXeDNDL1k0cW84M1JBRWMxR1dPTVFRSktXV2VlODZyZHM4WVBRYXp6cWt2dGNvTkFxZXJhTFpNZTh2dngzemFiWkRGWFIxbXVMTTNlZnoreVZpT1FOOSt6Yng4VnBhNmlsRXpHVURJR3VGVnhoaDFDZmovaTUyNkkrSEVRQUlZNERtbk1jN2pVYm1RNzFZbzFrZlNvamlnNlV6ZEpUVytSVndHaGZSR0ZINXVzQUN4a2k4UmFqRmxyWWVYWVhObjgrSFNqUCs0WEVuNGY3bTRhOVBPcUJMZkVncHd2R3VSWU5iVUlBS3pZN3J4Wm1pc0dLTXNJOVFpNHRrUHRxazZjWm91Y0VaRDMzWlpWZStYRVZMbTI5MlBiWGx1UjdSc0ErSXZIbnR2c0Q0cGZ2bWxiOTNCUFY2eWI0L2lOUE1lRlpKa0RvY096ZG9YRllpMVNQSS9XTFFmTkxGV2Nac3VrdjlCa3ZOK2pZcTVtZzNGd0s3UTZ0VnJQQmNYVFI2am1sKzJlaEE5SGVJNXVhWnIwcXhFZjErL1hrQ3J5U01NSStKVm1FMnNCM0hMRXJ0M1lnUkV6elhxTEdKVUdtVlJsK0RZbWFMeWhlMFd3cGNYTm0xR0Q3ZDYxUk95WVpodThEWlFOcWFhbXFHS3MxbkIvVDFPNXUxSmhqaG05ek40SkN6emlPZ1gxY29pWmRUTHptRzFTSjhRN3VaU3ZMcDZZTEIvL25mdTIvOGxiQVBEMXYzNm1LNlQ2ZHQrOHZTdnQ5MG5iSE5mNW9GOVRNcEd3SDB1c3JyZDl0VnVvcjFxV1p3VklUWnVTcGtGY1F1a0pET2psUXQwck5rMzZFcExjbC8yR1Zzbm5nYXdsK0xGQ1FJMlBBeit2RzBsVlFidlRFVDRwQ25RSDhXQzNMT0NrVDhOWUVoRFAwdHBYNDBhdXRIWnA5L254cU5zd0NORk5yeXp6M05PMlM4ZXpGVGRyT1BSZ0RDdTVpNG5Xc1hLN1JBSncxVzJHV2c2K09hamhPek1KSVV3OGVnZkdzRUdUc2FqS0NKL3RuOUNSekZublkwb290UjNIcmRWYlJHOVpTeUl2UEZzem5PTW56NVFXZnVlKzdkOTlDd0QyN2R2SHpVTkd2SGZiRURjNmRteVhKSXVmR3V4SmJrNm53a2tPb3lGUkVQeXFLb01rdHJHdzZnRXRoNE9YLzdHYU5zTnFxeVdUVXZxMHhNTXpMUlBObFp2ZXZPcEpVMHlnYTJXNldNT3BUQWJFZUJ5RXVZWjVNeWZRejhiOFhIOUl3eG1CUndPaWdIeVNpS2owbStHL2VSSk1jN0pkd3dDTGJKYzJnYUxwdXVIT1prdGt3WEhoSDRKQitiREJnelgvRXRocmFlR3kwc0g4cDZkQjFKcFdiendDZlFHWjd6SnMxc2dhN291Rk9GR1JrTVNvYzlZUXEwTXh0NXRlTzY1SFc0WUZ0dVhVWGMrYkxGZjF4ZmxzYVpKUStGNjAxLy9hN09HQ3MzZnY3Y1piQUhEdWluNzk2NC83RTcyQjZHQmZ5dDgwOUR0YzIvdjlaREswTVpPT2lTRy9HdUI1anVONERsaDlXQ2VEWE9tVnc0U01BTXFHUld0Vm5lUU1tendSVU9qM2E3cFVueSszVUxQc1dZV1F2Lzd3S29vYno0ZkVKMzlWQ29pY0ZndEhrVTl2ZWgrbUFIdERHdTVQUjNrdjdNT3NTWHdBQUtTVjRUTXJHZ0JNb0xUUk5LbWRxN2hjcWViTlVBcjdJeUh1bWNVY2JUWE5SdjZUZDhTYWExSHp5NXVBb3ZGeDBDSVJVS2hneDhvTmVGQVMwR2VpUVM2dWlPMVdPUkdlWngxVE92ZG1XQzJZNXpIRHovTmFobFZmWENyYlM0WEtLUUQ2MzZPUjRNSHNVa21mcTdVS2Y3RDNudWFxUXNFcjNienV2dnNBZnY1WW95ZnFsM2YwOWNTNmdqN2xCczl6UHhzSytpS3BSQmhGUWo3TWNWeEhJR2dMWS9rL1ZybENiWmVhQ0dDQ0FveGx5NTQ5bjNlOFVvTWV4d1QvOXdnb1N3ZDNBK25VVGxnaG9PNCtBSGdSMXdkOEhONFJEL09oM2lSSC9BclgyekxnMDZJSTIvMHF3c3lhMHBuVjdNSlJVWUFmRTQ5T3pSVWNOSnVudGFidUhzTWY5cDlaYS9jdWRyNnZkRVNibmdheFNTeVdVWDFQMk05bE1JWmhqOEJtV1VSdHBicVc5TG5sSTVaQW8ybVFmS0ZLQzZWYWtlZjVIOW0yKytyMFFtRmhxZFFhNzMvb3Z1bnhSNEUrOHNqNWlhVUw2dkxkSXlQOEo3VGJwR1FTdEpKbGIrVTUvTW0rVER6ZWxRaHQ5R255ZGxXVnRZQlBBMVdWUUdBRWZ3ZlhXYXVWNGNGMlhXcFZkVUtyRGVMYWpuZUc1L0VQU3cyU0xUWG9hZkQwbzU5YVkyTkZSa0NsdGhWbFZZN2h3VUVBYk5ycGxra2ZDUHI0cmRFZ3dpeE5xZHJ3b0s3VDE0RW5UNGNGY1hhcUNvaFo5RXRqTWZQaGh5OWNZbjIrS2YvbFBxcEV3NDFkbWFnd01Kam1jQ0tFUmNOR3V4R2lkMmd5am9nOEVpa0ZsajNac2FwbnhGTEx0S0JlMTBGdkdZYmowaU9GVW4xeVluSXhheHJ1azhtZzczWFRORnJQRzlQMk54OSsrQjFMeEM4SWdITW1oKzc0eXRkOXQyenNTbTdwVGZxbzUzMVFGSVJQZFhkRjRodjZ1MUFzSFBCelBJNXpHS2tDejBNNy82dURxOTJybjdWR1dYYTFXaTBMbGtvMXIyWFo5SGxWZ2U4YkZscFlxanFWZWF5VjFubzBzT0d3VHVQWEI0MWtTdVA4NmFnSUlBS1VTZ0NGaXRGWUFLVncvOGExdWFncno4NFFQUnJWaEVoUUpZbGlEWDVQVTlHdDZTZ25odjBjTzlpakFEU01NZXVoMFlGd2xybUZkbjg1UW9pQkVNNVhhczNHNmNsRk1qMjNWS0lJL1ZEaHhSZVBUaXhVbjN0K0p2L0NqLzdkcXJ1UWRqaU1Od2FOUHZ5VnZ4clkzaHZjZXNPMkFkL04xMjBBV1JLM1daYnpDVTJWTmtmQ1BpeExnc0JocG1FN2h6Z0xhcGdzc0dRUjVvYk5lQVNPRmFwZXFhR1RYOG9jL053dnlFdTJEZlJpQ2FqT2x1RDhkN1BqWm5RVStHQVE4Skp0SmgwSFBoSlU4Tzd1S0JjQ1JLN25PTnlqeWdoSkF1NDROdkdiaWg5S0hOZHp5dFdHVjJ1MHp2ZzA1WEhQZGNkZVBUSkZuMzkxb25WMHBqVDI3Ti84OFF5TFNuYzZwN1VDQVBiczJjZUZQMUxCSHduZmlRWUh0OUxacFVPYmVBaysycDJNYklxRWZiMkkwSnRsUllvRi9DcklrdEN4bmx0eHgxZ092T1VRcjY0VGgxRDRGWWZ4d2FydXplWnI3aWw5VGhsZGExQ3BVMEc5M2YzZmVvN0tReEhqZW5ha0JId29Rd2pjeldPNFBlekh2Q3l5UkZqVXJxdnVWTkJ0WXNsMm9kYlF3VER0SXM5eG83V0dNVGs1dHpScEcvU3BUUU0zblJvYkc0Zng4VEVZR3dOdi8vNjlIUzgrbTFPbjQzcGJ1WTE4NnpsNWEwSUxkU2ZEV3IxZXY5NTF5WjUwSWp5Y1NvWkRrc0QxaXFMQXk3SUk3SGpveEhOZ0x6emJGb1VaamczTG9iVnFrMVFzbXg0TSs5RVBDelZhbTFsMDZKbThXejl3T0RDN2Y2VHozUGkxZ09IbHNXWXFHaERpc29wVGVvdCtRdURnSTBFZkY1UkVGT0F3QkJoUnRSWit3bkU4c0d3YmJNZDFQRUxubG5MbDJzSlM2UVRQOGYrWVRJY1BIWGw5eGpoVDl5b2pYNzdua256TjdKSUI0RndoL3VHZi9VMThZeXExZGROQUt1M1Q1RzJtYWUrSlJnT1JaQ3lrQlB5cXl2TmMyL2hpOGVWT0JzQjJCYk1STEllNkNOQWN3bkE4WC9ITSthSnJsK3ZraENyUkh4Q0I1c3lHYVZpTHNkWmFmUFh6Z1lGWjgwTzNsSDNCaENCdTd2YWpVQUNFU3NYNW1NempEd1o4T0kwUjNRZ0EvWkt3SExIckhPRExiaHdMbStxNnBlZExOYk5jYVJSa1JmeWhZVGl2ajUrWVd6eStVRHI1SC8rUEwrVFdBdFozK2swbjh1LzQzU01qKzhTbTRHNE8rK1FIdDIzT0pMcmlvVTJLd3Qvbzk2bWhjTkNQTkZWbU9oS2ZEYU4yL0h3V202KzNDTlNheExac09vRjVlTEpTODdKbG5VNjRsTDd5eVZ1MTR2NzlBR3Nob001MUpaditSc2hxd20zeE1EL1lHK2RSUE1SSnRvYytCSlIrUUpaUW9FMk9kU2pKNWRZdUxHWlBxTjZ5YUxYV0pMcHV0RHhDZnJWWXFFK2NuTWd1Rkd2R2sxekRQVDR5c3JmamlwL1ZDclBEWWEvMnNXZnZveFNOUExwZmdLQ2dEZy82cGRucDZ0MlNMUHpPcHNHdXpOYk4vVGdlOFFjQTB5NEVLQ2lLUWp1dzFNbTFFbUVrbEZMSEJhZGhFS05ZY3gzRElpK29JdmRkeTBhVEMzVlNhamxTZHUvdDZJM28xMnJld2M3MmxHeW1ZaEdhVUFXdXQ5Ynl2cWhLK05aRW1PUENBUTZKUEpLQmdvelkybmNvUmViRzJiWURsdTJBYWJsbXBkcklUODhYMnY4SUlYOFhDZnNQMXBjTVBadXRHQ09QN0hIZ01uN090c09ocjBaMGI3Mkg3YWE5Ky9kajY1bkYyT2IrU00rdUhSdlVtM2NPVU5jajIvU205Ym1nWDl1VmpJY0VYdUJrMW1xdWZUUjBLTld6QkJTMUhjcnE2RXFPQzdQWmlsZXJON3lEaW9qL01TU0lNK01WY0I3WUJjYmJSZk9ZcXQvMUFNanBNQWkyYmZkNkR2bVVUMEVmU2tVNHY4aWpmdFl5UnhRUkVwZlA5NDVreDhLMHl5MWhpZXM2bmxFc041enBoVHd0RkNwWjAzU2VRb0x3ek9UcFhHT3hVWjlPUUgveGtVZDJ2eXRmSWV0b0VtdGIvbk4rTlRLQ1J3QndPdjBBZXVpaFhmUWYvdkZYR1ZYbVA5RFhHeHVPQkxWK3c3SjJhNHJTelFnb1VlVGJQQWl6VTFlTGhYT0lIR0k1bERSYVREZWdNWVRvcjZzdE9sdXNrVEZWRlErS0xXaU5iUVZ2TDFyK3FOTStTcm5CVWNDMlV2ZGJEbjk3Sk1CZkgvVnpHUUI2QzRmUkRuKzdzWFdiRXUvWW9tOHZQQVZxMjQ1WHJUVnB5N1RuUkpIN3AyYlRQSDFrYkFhT25ab3JMeFlhb3p0N2UxNDdKczJScm16V0d4a1p1ZVFGSUcrM2RwMERnTkxmK25UbVdtUGpiY0h2MjhlWndVM3lRQnFKMmVuV05rRGs4d09aNkxidXJuZ1lJOWdvaWFJaU14SmM1RHYrT05BS2xjdmlKN2JEb294ZTNiTGh4YUFmN1c4MWFhN1k4aGE5aWpTOWF4ZVFsOGF0Z1dpWVMvc0UxTjJ5eUtkNURuMHc1TU0rVlc0VE0rSmF2bDNRSm1ZY2o1cVdUVTNMTVNpaEV3dEw1Y3JDVXVXWTY1SHZENlRqUjE2ZU9FM1BISm9sVDB6TldlUDdSOVoyemwva2VuUUFBSXErTlBKdEtTMWlyVGVUYXNkOGFkTnhPQzdWZlBqaG15N1lrZkpDMm1Qa1ArL3pwVUtoOU5hQjdyQUwzZzNOdXZGUUtobkpkS2NpU2pDZ2FrS2JnY0tBdVE1MTc0b2I2VkcyMjZ1RXdsSzU2WlZiRmp3WmovRDdHQVcwVUxGL1YxWHd2U0VmamtzOHhCRkNVWjVqWWVMTy9PU1YyRHl6NkYyM1Rjem8yVnpGV3NwVnBsVlYvR3ZLd1d2VEM3WFNZckdRKzNkZi9kU3FvM1hubGQyS2ZSVUFYeklXNURVQW1LL3JubDJzNlNPUGZJbVZtSjN0SVBqT2tsODFBTDQwOGkwNTZNRU9UWlUrbFVsSElvSWdnR0ZZTTdiblBkNGM5cDBlT1NmTjZFS0xmYjYvLytaek11UDhtZEtwTGsyU2J4c2VTRWMxaGI4WktMMDNHUFFsVTRrSWhJSmF4NXpET2U5ajdkaXBhUk1URUp4UUJIeVkvYTFwa2hzQllGZ1drTUlZdWJWK204ajFQS2czRE1nWHE3UmFiWllFUVhqQ01LekRFOVA1WE0wMGY5MnQ5UzZPajI5MTM0Nlk2VVJ1RHozMm1CQXRCUWRFRVgrMkp4WHBZZTVVdGxBdTFnM2pKNnJodkRZeTh1VlZ4UWt1Q0lCL08vTGRyc0VOa1cxRHZha3VTZWF2YXhubXB4VlpEckttd0FSZ0llaFRmOURRV3hNejg2VzVoVnpsK0w5KytNRTE1N3V2Q0dEWFE0OEpld2JENnVCMUtYRit0cklMYy9pK2dVeWlLOU1kSFFqNDVHRlZrZjJhcG9DbVNNQjNTRUN4ZDdBekdRQXNqS0R0R1JBQ0NxT0gxeEMxYmh0MmhtbEJvOW1DbG1FMTlaWjFLbDhBdEtrMEFBQVV6VWxFUVZTc256azluYzBEUW8rbjA4R2pTMmNhNXVqb1pPdWIzM3huWW1ZMUFQamFmM2tpUEJSVWRtd2NTR1g4QVdsRHBhcnZrU1V4emVvbVROT3VhSnI4QkVKbzlQakVnams2Tm0yTlRTeE4vZnkvalo0RTJIL2VJK1pOQUtEb0QvN2tPNUcrZENDMGJXc1hON1NoQjgvUDUyKzJMZmR6eVdSa01CVVBCUVVCZHpObHpDd3pTcWpsRWJLUUs5YWExWnArV05Qa0h3WUMydmdycjV4RWg4YW1uTmZtRnNzLy8rYlhMcXJpcGYvVEk2RUh0dlNsdG0vcDlnazgzQzJKd24ycFJMaW52emNKOFdoUTRUa2N3eGdyZ3JDY24vQnVYT3g4dHgyUGtmd0dBQ3FWS3ZYV21la3NMQ3dWNXkzYis2a2tTd2NPbjVqWG56MDB2VGk2LzJ2MU56NGZ0WWJCRGQwM0VyajN4a3o0OXAwYnhaMDdCNkZSTlRZMVd0YWVXTVIzWFZjeTdNY1laemdPUzJ3aFBVSmNTbW0yWEcxVlp1WnlaSDZ4VkROdDQ1ZUJRT0NKV3NVdXppM1ZhLy8zdi85TStkeVUvZC9LQ3Y3YTEvY0hKQXdmVDhZRHR3MFBwdnhEQTEyZ3lNS0FiVHM3WlZrTStYM0xjZjJWaTUxNXR1T3dUMXlBUjd5c0pBaUhxL1hXNHBIeEtYeHljcWxZclRWL2NjTmcxd3RMUHNOSk5adnUzcjFyaTFldnZPK2pYL3J6alZ0Nms5dDNidXNKMzdCakVBZDk2a0NyMVhwQVZaWGhXQ1NBRlVsZ0x1UlpBbW9OMHI3QVQ4NjZjc1IxUGJkWXFWUERzRS80TlBHcHB1Rk92SHBrZ3I1OGRLcjYya1RoMkxQZitWZW5MdWJ0ZS9idDQrNWE4dkdoVGQzODBaZEhiNDBFUTNkdkh1cEtieHZ1UWNHZ212WmNza3NVK1pUUHA0QWtNSjdsTjI5elhCZDAzV0o5WXNFd1ROdXl2SWx5WFg5OWNyYVl6eFdyTHlMZWZ1cHJEKzk5WTFPKzhkTS8rOGErdUNTcHU2Tmg1WXZKV1BpT1REcXFwWk5oNHRjVWp1UHhjazdnZVZ5eVpVNmZ0YWNGd2hCWXJlbGtjYW1FeXRWR2tSTHlMRUg4THlkT0wxUnpGV04wNUk4L004czA3bHFGd3dpbzFGMCsva2EvZ2padEdrS3o1ZmwrVE9pbmVqUFJyVjNKY0pJUWNyTWtTV0cvSm9NaVM4QVNRaS9WeFFJM0RkMWtkazlGa29SWGx2TFYvUHhTNlhYaWtDZTNEY2RPSDVpZWhsZGZOK2pTUHpYZHRSSXpLMlA5WC8vME93T0R5ZkFOV3piMlJpU2Uzb1l4dWljVThxZVQ4UWdLQlZRc0NCelBWUDVLYzRWejU3anNDcmMvU01WaURneWtKRmVva29XbHNwNHJWRjlZek5lLy9jZi8wLzNmVy9uTkd4TDYwLy8zOFUwK1Zmank4R0R5dHI2dWFHOHdxS1UwVlpKVlJRWVdwVnVOTDg1MmlNdFNrMnlIY2RldWJsaVZRcWxlV2NwVkM2TE0vVGVKbDE2Wlh5eFZEcDArcy9RWC8rYUwrc1V1emg5KzQwbnB1b2dTSCtoTkJIaWViS3JWalM4bDRzRytkRHdVOVdseVFwSUVVUklGNE5kQVFMVXJmbHhHekxUbjRqaXV0NVFyVkN1ekM4V3BZRUQ3RHMvekoyWVhDclh4b2xXNkZNVE1ReU9QcWJkc0dPN2F1am5qcnpmcnQ5dTJ0N2NyRlVteFkxZFRwYkFnOENJcmt1UlpOYzRxZ0wyYzMrZ3h3RExieEc3b3h1TGNZbW4yOVpOekwvN0xyMzdpclZuQlRBUElxclpyeCthZWlFOFdyM05kOTE2L1h4MUl4a0o4d0svS0FzOXh6QVZiTFlIRHdHRFpMalIxZ3czQ3hCdytYS3UzWmhlemxWbUt1Y2Y3a3BHVEV4Tm52RU1UUlhyZ3lCSHp3TGRIVm1XMW5oYzBJeVA0WDhGd3BEc1Z2R25UaG1SRVU4VmRnT0RqNGFDdkt4a1BDd0dmSXZNQ3p5Mm5pTDl6ZmdJejZ0aVhIajNYSTgyV2FlWkxOYXRjcmhjVVNmeFJ5M0dPSERrMlczaDl0akw2elpFdmxDN2liRWREOTMxRC9OemRLZmtEbXphaXZqNmZzRkJ1REJIaWZtNmdKOUVWOHFzREZPQTZSUlpWbjZaMFJLZXpzYk1jUWRjbFhsTTN6VUtwNXRTYnJYbEZscDVvNnExamg0N05Gdi9sVis1NytpMGFZRmx6c0Z4TmdLLzk2ZDlkRnc1cjl3ME5wSVo2TTdHTVR4R3YxMVFsRmdob3lPOVRtQkRiM3dWWmpWWll0cklKV0paTEsvVW1hVFNOck4rdlB1NjVaSHpzMUl6Myt2aWNrU3RWam5mN3RHT1pETmpqNCtOMHJaR3c1Y3FhL2ZqUUtXOW5OQ3pmdDNHd3E3YzNIUm5VVkhHN3Fpb1JscHVnS1JKaWJPUzU0MThoWnRqaTZ5MlQxT282dEF5emF0anVzVnl1Y3ZLMUUzUFplc1Y4WE55SWp6MnlaOCthditNM01qS0MyOTE5NEp0eTNrYkRYYW53dHV1M0RLcWJOM1lKQWtLYjZ5M3pjNUdnTHhsbW5iVWtrU1VzcmtwSnJxaDlqMUNxNndhdDFuVzI2VXFXN1I1ZHpOZk9uSnJLemxRYnJTZTJkcEd4UFh2Mi9GWmg2bm5mOElmZitJWVVkVlBhcGcxZGNuYXhlQ3NuY0h2N000bk5XNFo3Y1U5WFJDS1VwakVnUHlzWFdHM3ExM0sxQ21Gbms0c1FOTXVWcGpVNXM0Um5Gd29WMjNaK0VvMkd2NThyVjB2WlJhUDA3Ly9vdmxVM09qeWZoRVpHdmlVSEFwcWEyWmhSWm1kekg1UmsvblA5UGNrTnd4dTZjVEllbERtZVMvTUkrOXFmTVVQUVRyVml5UmVXN1ZpbFNyTndlanJibUYwb1RycWU4NzIrbnN3ekUxTUxkak5iYkhZU1lIbnJ1Q2o2a3ovYkh4c1lqSVJUMFZDaVdxbDlYQlQ0VC9aMEoySjlQUWtjQ2FraXhweWZZKzJZV2JCcmxidUxiUzZiamQveHdMUnNzMUJxNUU3UExPcno4NldUbEpCOVE1blV3Zkg1b2pYVkl2cC8rcVA3MzFLUjliWVFXdzdNUElyR1NwRm9YencwZVAzbTNzRE9IWU00RkZMVGhXTDk5NE4rWldjOEZsSWtrWmN3Wmw4UVdYM3FGNldVc3VPQjVhK2JwbVZibHJOUUxEZG1weFpLUzRadS9MUnZxUHZKUW1QUnJKenlXdzg5dEt2akwyR3RhTFAyK1BQeHhFQlhzSC9IMW94LzU1WkJMRXQ4WDEwM3Z4QUtxTmNuNHNGMlhER2JyOExjWXA2V0svV3NSK2pqTGRONThkUnNybENhcjg2a2d2OGllekdCbThjZU95U0V3dzBwMHBlU0pzWm43ZytFdEk4UFpPTGR5WGd3TFV0Q3J5eUxFak5ZUlpGbngrdXFscjM5TVlCbGNzbXpiZGZLRjJ2ZVhMWklTcVhhQXFYa2UvV0dQVG94bTg4dkZJM3BiLzM1bDlvNUJHOFhzcit3amhrWndROHRwcm50bis3QjkyM2NDRE16cFZEVmhJOE85aWFHRS9GZ3hteVo5eWlxMUJVTStqaFpZcC85WFM0ZXZOQ0RseWZSUG5aWXZKeFVhN3BYYjdicWlNSWh3T2hYWjZaejVVckQrR1dvVjVud05ScjJ3WU5zR2dmSnlNZ2p0S1BTOHpZQnRaV1AzT0pESCs3cm81Tkx0VGh4dVkvMFp1TERBejNKOWthZG1zbkI0YkVwZXVyTVVrbHZHUy8waHNReFhaZWR0YVJhTGF2NXUvSGRkN01uOS9Qaml4T2JrNkhBSFp1RzBqRkU2UWNRd0M2L1h3dXlsSHBaRXJsT0txNldpU1ZLTGN0aFI2bHJXdmFTTEVuUDVncTEzT3ZqMDk3NG1mbGlxNjRmN0E3TEV4TUJ3MTROc1hTaGRYcUxKbU1UN05uNUVXMXpMS1hVN05wZ28yYitzNEcrK05idVZDUXVpa0tHNTdtZ0xJbkxpRjRWbnRzZ1dNNklJWVE0dG10V0czb3JWNmlWRlZuNmU1OVBlZUhVNUx6NXltc3o5UFJDS1V1VzBPSzN2NzI2TU9mNWpnZEdRTm0rWG0xeklpd09ENmZidDV3NmxZWFIwVk53Y0dyT2UvbUkyenI5MHo5YVUvSHE3aStOeUYxYUxMbDVLTkYxMnczRDNNQkFTcXhVR2g5MFBmTDVybFE0SGduNkZFSGtaWllzMjRtYVAxdnhBNmJGUEJLbllUdmViTEZVS3l6bWFpY0NQdVZiMkJObWprek9lSzhkUGU0ZFBKUFhPeUdXT2diQXVVTDkxLy9QZDdUK2VLTC9oczI5Zm8rUVhTejFLeGowWFpmcGlncWhnS2FJelBKdWMrZXJKM0FZRUZnd2c3bGVnc0RQTkhTek9ERzVTTVpQelR2MVJ1dnBaRVI3UE9MenpjL05GZHhzZHRSY3E4RzRLdXRxRlRlMWQzeDZsenpVRStmcnBVcTZWdFh2ajRTMGoyOFo3bFVIZTFPOFQ1UGpRR21QS0FvaTJ4U3JqVmF1WkFTek05NXhQYmVwRytaaXJtS1h5dlhqc2lUOFBTY0pyMDdPNUJxTmJHWHE0WWNmWEhObmxvc0N3SXJWRGJBSHpoUi8wT2VUbFp2N2UrTjl5YWgvTS9YUVJ6V2ZuSWxGQWhDTitOdis2MnF2RmF1V3VlS0dhYk4wS2FqVmRPSlJlc3l5dmNNejgvbjV5Ym5pS0RRcXYvZzNseUNlc05weG5lKytmL3Yxdi9hcmZQQ3UzdTdZclJ2N0U2eXc5anFSNTNZRUFqNGhHRkFSbzdQWmp1L0FhV3EvaG0yRVJ0T0FRcW5HZUlZNWhOQXY2azFqN1BSTVljRm9PaTl0SHhMbkNvVUNmYk5WMytsY0xnb0E1NzZNRVRnUGJrbG8xL1VseElyUjJPYTQ4S2xVSWp6VTB4MUxoQVBLVmxXUk5WWmNxc2hpUndSTzJ5LzN2TFlyYVR1ZVdhdnJScUZVcnptdTkxSWs2UHZKaWVscy9kZWpwNzBqRTluQ2ozOHlQZ2FMMzF6emJ1aEVlSC8rWDMrOGNXZ2czdCtUaXFVdHcvNG81dEVIazdHUUx4THlLN0lzS0d5bnJ5UytydmE1VE5Venc1akZUa3pUYmxxMmUzeDJzVlNZWDh5ZmNnbDZzanNUZWUza21Sbm4rRXQ1L1ZJUVMyM2pjTFdENitTK083N3lkZitOdmQwOW0vcmpzWWhmMmc0SVB0K2RpbmIxWkJKY0xCd1FlUjVIT1l5WU85YlI4WEJPTGgwcmxDbGFqanMzTlpOelRrOHRXdVZLYlV4VjVIOW9OdHlGeWJsODQ4V1NVeDI5Qk96YjhyejNjQTgrZEd2NDF1c0d0RHQyRGFKME1pRk56dVFmMUJUcFF6M3BTTnJ2VTlJY3hpenlpRmprY2JXdU1Yc3ltNVBMMm9XeVk4LzEzRksxV1oxYktOVHorY29zTC9EZk0xem45V05qdWVJemh5Y1hSdmRmSExGMnZqVzhMQUE0NTBYNC9xLytSYytHVk9UMm0zZHVpT3pjMG8vRFlYKzZaUmozeTdJNEhBcG9naUtMekNCYXp2N3E0R0lJTUUwYjJQRlFiK2lrWmRoenRhYng0dFJzdnJCVXFCL1JKUDduZCs3cXlqN3hSSU91S2IrdW5YQnhnTnU2TllISGMwZGplc1g2eUlhKzVQWWQyd2JFZ1o2VTZEbnVUWUJodTArVEZSYXRhOGVXVm5uOUpsNVBpVzI3WHJuYXBQbGloWGxCWmR2Mm5wM1BWdytQdmo2VlBUMWIrL1ZUMy83amkrSlBMalNrenFSK29hZWQ1KytNd05tMlRSZlN1M2JnKzI5STB1T1R4WXhwdVh2U3lmRDJlRFRRSmZEY0Zra1VFb3pBWVVrbW5lRGdISCtZQlVHOHBVTFZ5ZVVybnUyNmh4UkovbEcyVUp1ZW5pbk1ubDZhUHZGZlJ2N2dMYVhSN3pTZFBmL0xmL1p0MjVnYUh1aU9EUFoxeDlLR1pYNVNWZVFiVTRtSWxJeUZrS3FJN1B1cGJaSnN0ZDRPZTk4S2c2cnJKZ3ZnRkJER1kvbDhKZi9xK0RTWm5zM25YZHY5Y1RTbEhxck5WTTJ4TWMyNVdHTHBRa3QyMlFIdzVnR3dXb0hNam1oNiswQ3YxbXFaMngzSCtXd2lGcnd4azQ2cW9pakVlSjVqRFVrNnp2cHBoNXR0RnRGejJpMVJtcnFabjEwczZJMm1kVEFVVmgrM0xXLzI0Sy9HNE9qNGt2bURwNlpLTVA5WDUwa1QzOFA5MlRlK0hMbHVhOXd2Q0dKL1N6Y2VWQlRwbnY2ZWhCSU9xa2xKRkFJc2VpZ0tyUGkxczdKNHB1YVpkK082eERRdHA3eTRWRzdtQ3RXamZsWFpwMHJjK01GRGsyaDBmTVllbXl2blJ2ZWJEWUIzSnpIMFhRZkF1WUQ0My82dnYwOXU3SXBldDJWanFpOGE4UTBaTGZ0K3Y2YjBKZUpoM3M4SUhHWStNd0tudzBRUFprRXpZNHBaMFpUU000RFFhL09MeGZMUjR6TmticWsweXhIMFZEQXVUVTdPNkcxcWV1TlFCRzY4YVpBNldTdVlyemMrbklpSGJtWm5POGR4T3pCR0c5c2NnaXF2QVpRc1d0ZXUraUVOM1RCTDViclhNdTFKanVkK1hxMDBqaDgrTVQ4L1AxczQvQmNqWDh4ZmFLZGVycjlmVVFCQSs1eDlGS1VmZUlETFBuZDZrK2FUUHJOeG9HdnpZRjh5SlF2Y2pZb2krd04rRmFtcXhGeXAxYk5QWjZXMUVqbHJHUlpoZVhyWnBiS3I2OFlVb2ZDalhGa2ZtNXpKVWR2MllOTkFBclp2SDZESnNEOWFyeHYzOHlLK0l4WU5xZ0dmZ29TejNTZzdFZFRLZXczRHBvMW1pOVc2MTAzYk96eS9XTXpONThyakRkTjg0b1orOWRqQmcydUliRjVpSkhReXIwdjg2dDkrSENPZzBwQUtEUFVuZlhyTDN0a3luSC9SbTRuMWJ1aExCUkt4WUVRU2hUZU9oazQwd2twQWhYSGpudXRSMDdidGJMNWFuNTdOVzlsY21USnRrVTVGWUxBdkJWMkpNQ3RnOVdPTVZNYklkTVJ2c1BPZFdmUmVXOVd6VkhDN1V0RkwwM081NWx5Mk9DWHcrUDhMK3JWWHM2VldJMnRCODFMa0VGeUtCYmxxQU1DMEFiT09IMzMwQUxjRWM1RVF4Mi9mdGlVZDBCVHBWZzdESjROQlgyK21LNDZUc1JBbnl5TExpT0hXMUxXTXRJOEhxcmRNVnF6UkRrTXoxKzJjR01YWmZtaXJGeS96U0JneFk5bU9XNjQydld5MjVPVkxsUVVNK0ljdDJ6dDBZbUsrWEtzWjR6N29LVE9QcE8xL3J6SnRlL1dqV051ZFZ3OEF6aDMveUFqK0V2U0oyd2E3dVBtSnhhMmFMSHh3WUNDVjNybXRINUx4Y0JlbDVHYWU0d2FEQVphanlIcFhkblk2c0FWanUzVTVtYTFkZTlTbVg5K3VDZWI1Ukx1Y1F3RFVjVnhhYjdhUWFkdlRIQlorVmFrM0Y4ZFB6dEtqNDlPRmxtay9tK2dPbjVyWEJUdGFmdG01MG1Ickt4RUhXQnNzei9sVi8rNFIrYlpkYWQ4dHd6SGhqcDAzMHFiWjJHUzQ3bWZqRWY4SGV0SlJueVR5UGJ6QSt5VDJDZE0xcEg2dFpZRE1vbWZFak9lUnVrZko0bEsrVXN2bHE0ZDRVZGdmOHltblJvL1B3aThPblhhLzlkSnNBdzVjUktiVFdnYlg0Vyt1VGczd0RwTm90N0FiQ1BYMzk4U1RmbFVZMW5YckMrR2d0aU9WalBCK255eXpiQi9XbEdPMTJUU3JsZGR5WVdlYnRmUk0wN2JtczBXdld0TmY4NnZLOTF6c0haczZYU3llS2VhbUxycmlaN1VEdWtUM3ZlY0FzRUpBRFE0TzR0ZE8xbGhwK2QyOXZkSEJkQ3pVWnp2MmZUNU5UVVRDckltRDFsRkk5cDNreVZ5NVJ0T0VjclVCdW00c1NiTDA5RksrT2pzMWt6dGxXL1lMUXowZm15c1U5bDgwTVhPSjFyU2p4N3puQUhEdTdCNTY2REZoOHkxOS9zR2VpR2EwOUEwTjNmNUNiMWU4TjlQVmpzOFBLckxrWTFuTmlpeDBIRXRvZitQSHNsbU9JQXM1bTRibFRHUnpsZXo4WW5GRzA2Uy9WekYzWm15dXJKOTRlYVp4cVlpWmpsYnVFdDM4bmdiQWI4bmdrMS8zLzlGTnllR2J0L1Q1VllYZkJRZy9HSThFQi9wN2t5Z2VEWW9jaHdOQXFkSW1vTjRtM3J5YzF1NjFxVmpMY2x6V2ltMStzYVFYUzdVbG5oZjJtWVMrT25aaXJ2S3o1MDVQZE5LSzdSS3QxV1Y1elBzSEFPZUk1NzUvL3VjYk52YkZiNzl4UjEvNmxoczJzaDZHYWRPeTcrUTVmRjA0NUdPaFhCWlZPcWNwYXpzOWpWbjBYcVd1MDFLNURzMm1zYVFiNWt1TFM5VWp4MDdNRjdLVjFzSHYvT1ZERXhlUkNuNVpGdkJpSC9xK0JBRHJicm9iMG1MUHptM2NKMjRkSXVPbmwvb3RpenlZaWdYdnlHUmlJVXJwSnA3bjRpd1prMTJXWlRNL3ZrQW9aV3ErTW5aeWxrek81bVphVGV1blBVT0pGeVptc3E1UG43UkdSa2JlK05EQ3hRcithdm45K3hJQWJ4YnVYLzdsUGlVOUVFOE85S2Fpb2lUMlZ5ck5MMFJDL2hzeTZXaWIwV0ZoNG9iZUd0VmtkYi9sa0luUm8yZmd5T3VUeHVHcFJ1NmwvZjk3Ung5aHVsb1dkclhqdUNZQTBJN1luQzE2ZWZTdjlvZGpvZUR0VzRiUy9jTWJ1b0I5OVdobXZnQ0xpK1dwVXEzNXl2Lzh4WHZ6eTEzTDJLOVcxMlJodGNLK0d1KzdaZ0R3aHZBcFJZOTljNVFQMzZtZ25TTDcrZ29BTzlpVitYbHY5KzUzcHpIVDFRU0VhdzhBVjVQMHI0S3hyQVBnS2xpRUt6bUVkUUJjU2VsZkJlOWVCOEJWc0FoWGNnanJBTGlTMHI4SzNyME9nS3RnRWE3a0VOWUJjQ1dsZnhXOGV4MEFWOEVpWE1raHJBUGdTa3IvS25qM09nQ3Vna1c0a2tOWUI4Q1ZsUDVWOE81MUFGd0ZpM0FsaC9EL0F3d0t4d2Z3cFJoaEFBQUFBRWxGVGtTdVFtQ0MiLz4KICA8L3N2Zz4=';

	/**
	 * Main actions.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function actions() {
		$menu_action = is_multisite() ? 'network_admin_menu' : 'admin_menu';
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );
		add_action( $menu_action, array( __CLASS__, 'register_admin_menu' ) );
		add_action( $menu_action, array( __CLASS__, 'add_events_count' ), 100 );
		add_filter( 'plugin_action_links_' . MFM_BASE_NAME, array( __CLASS__, 'shortcut_links' ), 10, 1 );
		add_action( 'admin_init', array( __CLASS__, 'activation_redirect' ) );

		add_action( 'admin_notices', array( __CLASS__, 'plugin_was_updated_banner' ), 10, 3 );
		add_action( 'network_admin_notices', array( __CLASS__, 'plugin_was_updated_banner' ), 10, 3 );
		add_action( 'wp_ajax_dismiss_mfm_update_notice', array( __CLASS__, 'dismiss_update_notice' ) );
	}

	/**
	 * Redirect to settings area.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function setup_admin_redirect() {
		update_site_option( MFM_PREFIX . 'redirect_after_activation', true );
	}

	/**
	 * Redirect users to the plugins settings page upon activation.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function activation_redirect() {
		if ( is_admin() && get_site_option( MFM_PREFIX . 'redirect_after_activation', false ) && ! get_site_option( MFM_PREFIX . 'active_version', false ) ) {
			delete_site_option( MFM_PREFIX . 'redirect_after_activation' );
			$admin_url = is_multisite() ? network_admin_url( 'admin.php?page=file-monitor-admin' ) : admin_url( 'admin.php?page=file-monitor-admin' );
			wp_safe_redirect( esc_url( $admin_url ) );
			exit();
		}
	}

	/**
	 * Handle scripts and CSS.
	 *
	 * @param  string $admin_page_slug - Current slug.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function load_scripts( $admin_page_slug ) {
		wp_register_style( 'mfm-admin-global-css', MFM_WP_URL . 'assets/css/mfm-admin-global-css.css', false, '1.0.0' );
		wp_enqueue_style( 'mfm-admin-global-css' );

		$screen = get_current_screen();

		$our_slugs = array(
			'toplevel_page_file-monitor-admin',
			'toplevel_page_file-monitor-admin-network',
		);

		$is_sub_slug = str_contains( $screen->base, 'file-monitoring' );

		if ( in_array( $screen->base, $our_slugs, true ) || $is_sub_slug ) {
			$data_array = array(
				'ajaxURL'                   => admin_url( 'admin-ajax.php' ),
				'adminEmail'                => get_bloginfo( 'admin_email' ),
				'excluded_directory_markup' => '<span><input type="checkbox" name="mfm-settings[excluded_directories][]" id="" value="" checked=""><label for=""></label></span><br>',
				'ignored_directory_markup'  => '<span><input type="checkbox" name="mfm-settings[ignored_directories][]" id="" value="" checked=""><label for=""></label></span><br>',
				'file_display_markup'       => '<span>File <span data-change-type-holder></span>: <span data-file-path-holder></span> <div class="mfm_file_actions_panel"><a href="#" data-mfm-update-setting class="hint--left" aria-label="Ignore file from future  scans"><span class="dashicons dashicons-insert"></span></a></div> <span class="mfm-action-spinner"><div class="icon-spin"><span class="dashicons dashicons-admin-generic"></span></div></span></span>',
				'status_route'              => get_rest_url( null, 'mfm-scan-status/get-status' ),
				'wp_rest_nonce'             => wp_create_nonce( 'wp_rest' ),
				'fileInvalid'               => esc_html__( 'Filename cannot be added because it contains invalid characters.', 'website-file-changes-monitor' ),
				'extensionInvalid'          => esc_html__( 'File extension cannot be added because it contains invalid characters.', 'website-file-changes-monitor' ),
				'dirInvalid'                => esc_html__( 'Directory cannot be added because it contains invalid characters.', 'website-file-changes-monitor' ),
				'valueAlreadyExists'        => esc_html__( 'Cannot be added as value is already found.', 'website-file-changes-monitor' ),
				'dirAlreadyExcluded'        => esc_html__( 'Directory is currently marked as excluded, please remove from the exclusion list and re-save to continue.', 'website-file-changes-monitor' ),
				'evenmoreItems'             => esc_html__( 'further changes found.', 'website-file-changes-monitor' ),
				'continueLoading'           => esc_html__( 'Continue loading changes.', 'website-file-changes-monitor' ),
				'youMayContinue'            => esc_html__( '- You may navigate away from this page at anytime.', 'website-file-changes-monitor' ),
				'basepath'                  => ABSPATH,
				'eventPageURL'              => is_multisite() ? network_admin_url( 'admin.php?page=file-monitor-admin' ) : admin_url( 'admin.php?page=file-monitor-admin' ),
				'settingsPageURL'           => is_multisite() ? network_admin_url( 'admin.php?page=file-monitor-settings' ) : admin_url( 'admin.php?page=file-monitor-settings' ),
				'wizardIntroTitle'          => esc_html__( 'Welcome to Melapress File Monitor', 'website-file-changes-monitor' ),
				'wizardIntroText'           => esc_html__( 'Thank you for choosing our plugin. The plugin will now take you through some basic settings so you can get started with our plugin right away.', 'website-file-changes-monitor' ),
				'prevStepLabel'             => esc_html__( 'Back', 'website-file-changes-monitor' ),
				'nextStepLabel'             => esc_html__( 'Continue', 'website-file-changes-monitor' ),
				'wizardOutroTitle'          => esc_html__( 'You are all set...', 'website-file-changes-monitor' ),
				'wizardOutroText'           => esc_html__( 'You are all done and the plugins settings are saved. Click the "Complete setup & run scan" button below to lauch your first scan. The first scan will start automatically as soon as you close this wizard.', 'website-file-changes-monitor' ),
				'scanInProgressLabel'       => esc_html__( 'Scan underway', 'website-file-changes-monitor' ),
				'startScanLabel'            => esc_html__( 'Scan file scan', 'website-file-changes-monitor' ),
				'clickToViewLabel'          => esc_html__( 'Click to view changes', 'website-file-changes-monitor' ),
				'clickToHideLabel'          => esc_html__( 'Click to hide changes', 'website-file-changes-monitor' ),
				'isWFCMDataFound'           => ! empty( get_site_option( 'wfcm_version' ) ),
				'MFMWizardPanelMarkup'      => '<div id="mfm-wizard-old-data-found"><h3>' . esc_html__( 'Lets clear things out', 'website-file-changes-monitor' ) . '</h3><p>' . esc_html__( 'We detected data from our previous (now obsolete) WFCM plugin. As Melapress File Monitor is all-new, this data will purged once the plugin guide you through the rest of the setup.', 'website-file-changes-monitor' ) . '</p></div>',
				'expandListBelowAmount'     => 3,
			);

			/**
			 * Scripts
			 */
			// Only load scan manager on the main file monitor admin page (single site and multisite).
			if ( in_array( $screen->base, $our_slugs, true ) ) {
				wp_enqueue_script( 'mfm-scan-manager-js', MFM_WP_URL . 'assets/js/mfm-scan-manager.js', array( 'jquery' ), MFM_WP_VERSION, true );
			}

			wp_enqueue_script( 'mfm-admin-js', MFM_WP_URL . 'assets/js/mfm-admin.js', array( 'jquery' ), MFM_WP_VERSION, true );

			/**
			 * Styles
			 */
			wp_register_style( 'mfm-admin-css', MFM_WP_URL . 'assets/css/mfm-admin-css.css', array(), '1.0.0' );
			wp_enqueue_style( 'mfm-admin-css' );

			wp_localize_script( 'mfm-admin-js', 'mfmJSData', $data_array );
		}
	}

	/**
	 * Add admin menu and sub menus.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function register_admin_menu() {
		$notification_count = self::get_current_notices_count();

		add_menu_page(
			__( 'File Monitoring', 'website-file-changes-monitor' ),
			$notification_count ? sprintf( 'File Monitoring <span style="position: absolute; margin-left: 3px;" class="awaiting-mod">%d</span>', $notification_count ) : __( 'File Monitoring', 'website-file-changes-monitor' ),
			'manage_options',
			'file-monitor-admin',
			array( __CLASS__, 'file_monitor_admin' ),
			self::$icon,
			80
		);

		$settings = add_submenu_page(
			'file-monitor-admin',
			__( 'Settings', 'website-file-changes-monitor' ),
			__( 'Settings', 'website-file-changes-monitor' ),
			'manage_options',
			'file-monitor-settings',
			array( __CLASS__, 'settings_admin' )
		);

		add_submenu_page(
			'file-monitor-admin',
			__( 'Help & Contact Us', 'website-file-changes-monitor' ),
			__( 'Help & Contact Us', 'website-file-changes-monitor' ),
			'manage_options',
			'file-monitor-help',
			array( __CLASS__, 'help_admin' )
		);

		add_action( 'load-' . $settings, array( __CLASS__, 'save_options' ) );
	}

	/**
	 * Check if current tab is active.
	 *
	 * @param string $this_tab - Lookup.
	 * @param string $current_tab - Current tab.
	 *
	 * @return boolean
	 *
	 * @since 2.0.0
	 */
	public static function is_active_tab( $this_tab = 'all-events', $current_tab = 'all-events' ) {
		if ( $this_tab === $current_tab ) {
			return 'nav-tab-active';
		}
		return false;
	}

	/**
	 * Add count of file events to dashboard menu item.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function add_events_count() {
		$events_count = ( ceil( DB_Handler::get_events_count() ) > 99 ) ? '99+' : ceil( DB_Handler::get_events_count() );

		if ( $events_count > 0 ) {
			ob_start(); ?>
			<span class="update-plugins">
				<span class="events-count" id="mfm-inline-count"><?php echo esc_attr( $events_count ); ?></span>
			</span>
			<style type="text/css">
			#toplevel_page_file-monitor-admin .update-plugins {
				position: absolute !important;
				left: auto;
				margin-left: 4px;
			}
			</style>
			<?php
			$count_html = ob_get_contents();
			ob_end_clean();

			global $menu;
			foreach ( $menu as $key => $value ) {
				if ( 'file-monitor-admin' === $menu[ $key ][2] ) {
					$menu[ $key ][0] .= ' ' . $count_html; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					break;
				}
			}

			add_action( 'admin_notices', array( __CLASS__, 'changes_available_notice' ) );
		}
	}

	/**
	 * Admin markup.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function file_monitor_admin() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$current_tab          = isset( $_GET['tab'] ) ? sanitize_textarea_field( wp_unslash( $_GET['tab'] ) ) : 'all-events'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$last_scan_time       = ( get_site_option( MFM_PREFIX . 'last_scan_time', 0 ) > 0 ) ? Directory_And_File_Helpers::timeago( get_site_option( MFM_PREFIX . 'last_scan_time', 0 ) ) : 'Never';
		$next_scan_time       = Settings_Helper::get_next_scan_time();
		$scaner_running       = ( get_site_option( MFM_PREFIX . 'scanner_running', false ) ) ? 'mfm-scan-is-active' : 'mfm-scan-is-idle';
		$initial_setup_needed = ( get_site_option( MFM_PREFIX . 'initial_setup_needed', false ) ) ? true : false;
		$button_label         = ( 'yes' === Settings_Helper::get_setting( 'logging-enabled' ) ) ? esc_html__( 'Start file scan', 'website-file-changes-monitor' ) : esc_html__( 'File scanning disabled', 'website-file-changes-monitor' );
		$button_class         = ( 'yes' === Settings_Helper::get_setting( 'logging-enabled' ) ) ? '' : 'disabled';

		if ( $initial_setup_needed ) {
			?>
			<div id="mfm-setup-wizard-wrapper">
				<div id="mfm-setup-wizard" data-finish-nonce="<?php echo esc_attr( wp_create_nonce( MFM_PREFIX . 'finish_setup_wizard' ) ); ?>">
					<a href="#mfm-close-wizard" data-cancel-wizard-nonce="<?php echo esc_attr( wp_create_nonce( MFM_PREFIX . 'cancel_setup_wizard' ) ); ?>"><span class="dashicons dashicons-no-alt"></span></a>
					<img class="logo" src="<?php echo esc_url( trailingslashit( MFM_BASE_URL ) . 'assets/img/mfm-logo.png' ); ?>">
					<div id="mfm-setup-wizard-content"><form></form></div>
					<div id="mfm-wizard-controls">
						<a href="#prev" class="button button-primary mfm-button-primary"><?php esc_html_e( 'Previous Step', 'website-file-changes-monitor' ); ?></a> <a href="#next" class="button button-primary mfm-button-primary"><?php esc_html_e( 'Next Step', 'website-file-changes-monitor' ); ?></a>
					</div>
				</div>
			</div>
			<?php
		}

		?>
		<div class="wrap <?php echo esc_attr( $scaner_running ); ?>">

			<nav class="nav-tab-wrapper mfm-nav-tab-wrapper file-events-tabs">
				<a href="?page=file-monitor-admin&tab=all-events" class="nav-tab <?php echo esc_attr( self::is_active_tab( 'all-events', $current_tab ) ); ?>" style="margin-left: 0"><?php esc_html_e( 'Recent Events', 'website-file-changes-monitor' ); ?></a>
				<a href="?page=file-monitor-admin&tab=modified-events" class="nav-tab <?php echo esc_attr( self::is_active_tab( 'modified-events', $current_tab ) ); ?>"><?php esc_html_e( 'Files Modified Events', 'website-file-changes-monitor' ); ?></a>
				<a href="?page=file-monitor-admin&tab=added-events" class="nav-tab <?php echo esc_attr( self::is_active_tab( 'added-events', $current_tab ) ); ?>"><?php esc_html_e( 'Files Added Events', 'website-file-changes-monitor' ); ?></a>
				<a href="?page=file-monitor-admin&tab=removed-events" class="nav-tab <?php echo esc_attr( self::is_active_tab( 'removed-events', $current_tab ) ); ?>"><?php esc_html_e( 'Files Removed Events', 'website-file-changes-monitor' ); ?></a>
				<a href="?page=file-monitor-admin&tab=permissions-events" class="nav-tab <?php echo esc_attr( self::is_active_tab( 'permissions-events', $current_tab ) ); ?>"><?php esc_html_e( 'Permissions Changed Events', 'website-file-changes-monitor' ); ?></a>
				
				<?php
				$num_of_pages = ceil( DB_Handler::get_events( true, 0, 0, str_replace( '-events', '', $current_tab ) ) / Settings_Helper::get_setting( 'events-view-per-page', 30 ) );
				$pagenum      = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				$page_links = paginate_links(
					array(
						'base'      => add_query_arg( 'pagenum', '%#%' ),
						'format'    => '',
						'prev_text' => __( '&laquo;', 'text-domain' ),
						'next_text' => __( '&raquo;', 'text-domain' ),
						'total'     => $num_of_pages,
						'current'   => $pagenum,
					)
				);

				if ( $page_links ) {
					?>
					<div class="tablenav" style="width: 99%;"><div class="tablenav-pages" style="margin: 1em 0"><?php echo wp_kses_post( $page_links ); ?></div></div>
					<?php
				}
				?>

				<div id="mfm-per-page-input">
					<?php esc_html_e( 'Display', 'website-file-changes-monitor' ); ?> <input id="number" type="number" value="<?php echo esc_attr( Settings_Helper::get_setting( 'events-view-per-page', 30 ) ); ?>" data-event-update-nonce="<?php echo esc_attr( wp_create_nonce( MFM_PREFIX . 'inline_settings_update' ) ); ?>"/> <?php esc_html_e( 'events per page', 'website-file-changes-monitor' ); ?>
				</div>
			</nav>

			<form id="mfm-file-scanning-controls">
				<input type="submit" name="run_tool" id="run_tool" data-nonce="<?php echo esc_attr( wp_create_nonce( MFM_PREFIX . 'start_scan_nonce' ) ); ?>" class="button button-primary mfm-button-primary <?php echo esc_attr( $button_class ); ?>" value=" <?php echo esc_attr( $button_label ); ?>">
				<p class="last-scan-time"><?php esc_html_e( 'Last scan time:', 'website-file-changes-monitor' ); ?> <?php echo esc_attr( $last_scan_time ); ?> | <?php esc_html_e( 'Showing results from last', 'website-file-changes-monitor' ); ?> <strong><?php echo esc_attr( Settings_Helper::get_setting( 'purge-length', 1 ) ); ?></strong>  <?php esc_html_e( 'scan(s)', 'website-file-changes-monitor' ); ?> <span class="mfm-info-hint hint--right" aria-label="<?php esc_html_e( 'You control how much scan data to keep in the plugin from the plugin\'s settings.', 'website-file-changes-monitor' ); ?>"><span class="dashicons dashicons-warning"></span></span></p>

				<div id="mfm_events_search_wrapper">
					<input type="text" data-mfm-event-search-input placeholder="Enter directory / file"> <a href="#" class="button button-primary mfm-button-primary mfm-run-event-search" data-nonce="<?php echo esc_attr( wp_create_nonce( MFM_PREFIX . 'event_lookup_nonce' ) ); ?>"><?php esc_html_e( 'Search for event', 'website-file-changes-monitor' ); ?></a>
				</div>
				<a href="#" class="button button-primary mfm-button-primary mfm-mark-all-read"><?php esc_html_e( 'Mark all as read', 'website-file-changes-monitor' ); ?></a>
				<p class="next-scan-time">  <?php esc_html_e( 'Next scheduled scan time:', 'website-file-changes-monitor' ); ?> <?php echo esc_attr( $next_scan_time ); ?> 
				<span class="mfm-info-hint hint--left" aria-label="<?php esc_html_e( 'You can update the scan schedule within the settings', 'website-file-changes-monitor' ); ?>"><span class="dashicons dashicons-warning"></span></span></p>				
			</form>

			<div id="mfm_status_monitor_bar" class="card">
				<strong id="data-readout"></strong>
				<div id="bg-spin"><span class="dashicons dashicons-admin-generic"></span></div>
			</div>

			<div id="mfm_inline_notification_bar" class="card">
				<strong></strong>
			</div>

			<div id="mfm_inline_mark_read_bar" class="card">
				<a href="#all-events" data-nonce="<?php echo esc_attr( wp_create_nonce( MFM_PREFIX . 'inline_settings_update' ) ); ?>" class="button button-primary mfm-button-primary"><?php esc_html_e( 'Mark ALL events as read', 'website-file-changes-monitor' ); ?></a> <a href="#current-events" class="button button-primary mfm-button-primary"><?php esc_html_e( 'Mark currently visible events as read', 'website-file-changes-monitor' ); ?></a> <a href="#cancel"  class="button button-secondary"><?php esc_html_e( 'Cancel', 'website-file-changes-monitor' ); ?></a>
			</div>

			<?php self::events_markup(); ?>
		</div>
		<?php
	}

	/**
	 * Overall markup for events page.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function events_markup() {
		?>
		<div>        
				<?php
				$current_view = 'all';

				if ( isset( $_REQUEST['tab'] ) && ( 'modified-events' === $_REQUEST['tab'] || 'added-events' === $_REQUEST['tab'] || 'removed-events' === $_REQUEST['tab'] || 'permissions-events' === $_REQUEST['tab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$current_view = sanitize_textarea_field( str_replace( '-events', '', sanitize_key( wp_unslash( $_REQUEST['tab'] ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				}

				$pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$limit   = Settings_Helper::get_setting( 'events-view-per-page', 30 );
				$offset  = ( $pagenum - 1 ) * $limit;

				$events = DB_Handler::get_events( false, $limit, $offset, $current_view );

				echo '<div id="mfm-events-wrap">';
					echo self::create_events_list_markup( $events ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '</div>';
				?>
		</div>
		<?php
	}

	/**
	 * Create neat event list based on given events.
	 *
	 * @param array $events - Events array.
	 *
	 * @return string - HTML Markup.
	 *
	 * @since 2.0.0
	 */
	public static function create_events_list_markup( $events ) {
		$plugin_list    = Directory_And_File_Helpers::create_plugin_keys();
		$core_file_keys = Directory_And_File_Helpers::create_core_file_keys( true );
		$current_view   = 'all';

		if ( isset( $_REQUEST['tab'] ) && ( 'modified-events' === $_REQUEST['tab'] || 'added-events' === $_REQUEST['tab'] || 'removed-events' === $_REQUEST['tab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$current_view = sanitize_textarea_field( str_replace( '-events', '', sanitize_key( wp_unslash( $_REQUEST['tab'] ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		ob_start();

		if ( ! empty( $events ) ) {
			foreach ( $events as $event ) {

				$items      = explode( ',', $event['data'] );
				$file_array = maybe_unserialize( $items );
				?>

				<div class="mfm_event_list_item card" data-event-id="<?php echo esc_attr( $event['id'] ); ?>" data-event-path="<?php echo esc_attr( $event['path'] ); ?>" data-event-update-nonce="<?php echo esc_attr( wp_create_nonce( MFM_PREFIX . 'inline_settings_update' ) ); ?>">
					<div class="mfm_directory_actions_panel"><a href="#" class="hint--left" aria-label="Mark as read" data-mfm-mark-as-read data-read-directory="<?php echo esc_attr( maybe_serialize( $event['path'] ) ); ?>"><span class="dashicons dashicons-saved"></span></a></div>
					<?php
						Events_Helper::create_list_label( $event['path'], $plugin_list, $core_file_keys, Events_Helper::create_event_type_label( $event['event_type'], $current_view, true ) );
						Events_Helper::create_event_type_label( $event['event_type'], $current_view );

					if ( ! empty( maybe_serialize( $event['path'] ) ) ) {
						?>
							<strong><?php esc_html_e( 'Directory:', 'website-file-changes-monitor' ); ?> </strong><?php echo esc_attr( maybe_serialize( $event['path'] ) ); ?> <div class="mfm_directory_actions_panel"><a href="#" class="hint--left" aria-label="Ignore directory from future scans" data-mfm-update-setting data-exclude-directory="<?php echo esc_attr( maybe_serialize( $event['path'] ) ); ?>"><span class="dashicons dashicons-insert"></span></a></div><br>
							<?php
					}

					/**
					 * Scan-only event types are purely timestamps with no file data, so let's skip rendering
					 * the file list container to avoid showing a redundant "Empty directory" box.
					 *
					 * Scan-only event types are labelled in Events_Helper::get_event_label_array()
					 */
					$scan_only_events = array( 'file-scan-started', 'file-scan-complete', 'file-scan-aborted' );

					// Only render file list if there are items to show, the first item in the array is not empty, and the event type is not a scan-only event.
					if ( ! empty( $items ) && '' !== $file_array[0] && ! empty( \maybe_unserialize( $file_array[0] ) ) && ! in_array( $event['event_type'], $scan_only_events, true ) ) {
						?>
							<div class="mfm_event_item_file_list_container">
								<div class="mfm_event_item_file_list_wrapper">
								<?php Events_Helper::create_file_list( $items, $current_view, $event['id'] ); ?>
								</div>
							</div>
							<?php
					}
					?>
						<strong><?php esc_html_e( 'Time:', 'website-file-changes-monitor' ); ?> </strong><?php echo wp_kses_post( Directory_And_File_Helpers::timeago( $event['time'] ) ); ?>
					</div>
				<?php
			}
		}
		$tmp = trim( ob_get_contents() );
		ob_end_clean();
		return $tmp;
	}

	/**
	 * Settings area content.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function settings_admin() {
		require_once MFM_WP_PATH . 'includes/admin/html-settings-markup.php';
	}

	/**
	 * Help area content.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function help_admin() {
		require_once MFM_WP_PATH . 'includes/admin/html-help-markup.php';
	}

	/**
	 * Save plugin settings.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function save_options() {
		$nonce = isset( $_POST['mfm-settings-save-nonce'] ) ? sanitize_key( wp_unslash( $_POST['mfm-settings-save-nonce'] ) ) : '';
		if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $nonce, 'mfm-settings-save' ) || ! isset( $_POST['mfm-settings'] ) ) {
			return;
		}

		$expected = Settings_Helper::get_mfm_settings();
		$posted   = wp_unslash( $_POST['mfm-settings'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( isset( $posted['scan-hour'] ) ) {
			$posted_scan_time  = $posted['scan-hour'] . ':00' . ' ' . strtoupper( $posted['scan-hour-am'] ); // phpcs:ignore Generic.Strings.UnnecessaryStringConcat.Found
			$current_scan_time = sprintf( '%02d', get_site_option( MFM_PREFIX . 'scan-hour', Settings_Helper::get_settings_default_value( 'scan-hour' ) ) ) . ':00' . ' ' . strtoupper( get_site_option( MFM_PREFIX . 'scan-hour-am', Settings_Helper::get_settings_default_value( 'scan-hour-am' ) ) ); // phpcs:ignore Generic.Strings.UnnecessaryStringConcat.Found

			if ( 'weekly' === $posted['scan-frequency'] ) {
				$posted_scan_time  .= ' ' . Settings_Helper::convert_to_day_string( $posted['scan-day'] );
				$current_scan_time .= ' ' . Settings_Helper::convert_to_day_string( get_site_option( MFM_PREFIX . 'scan-day', Settings_Helper::get_settings_default_value( 'scan-day' ) ) );
			}

			if ( $posted_scan_time !== $current_scan_time ) {
				do_action( MFM_PREFIX . 'scan_time_updated', $current_scan_time, $posted_scan_time );
				Settings_Helper::clear_scan_schedule();
			}
		}

		wp_clear_scheduled_hook( Cron_Handler::$schedule_hook );

		foreach ( array_keys( $expected ) as $expected_key ) {
			if ( isset( $posted[ $expected_key ] ) ) {
				if ( ! Settings_Helper::save_setting( $expected_key, $posted[ $expected_key ] ) ) {
					self::$settings_validation_errors[] = $expected_key;
					add_action( 'admin_notices', array( __CLASS__, 'settings_cannot_save_notice' ) );
					return;
				}
			} elseif ( ! Settings_Helper::save_setting( $expected_key, 'no' ) ) {
					self::$settings_validation_errors[] = $expected_key;
					add_action( 'admin_notices', array( __CLASS__, 'settings_cannot_save_notice' ) );
					return;
			}
		}

		add_action( 'admin_notices', array( __CLASS__, 'settings_saved_notice' ) );
	}

	/**
	 * Add notice upon setting save.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function settings_saved_notice() {
		?>
		<div class="notice notice-success is-dismissible mfm-settings-notice">
			<p><?php echo esc_html__( 'Settings updated', 'website-file-changes-monitor' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Add notice upon setting save.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function settings_cannot_save_notice() {
		$contextual_messages = '';
		$possible_errors     = self::$settings_validation_errors;
		if ( ! empty( $possible_errors ) ) {
			foreach ( $possible_errors as $index => $setting_key ) {
				$message = Setting_Validator::get_validation_error_message( $setting_key );
				if ( ! empty( $message ) ) {
					$contextual_messages .= '<p>' . $message . '</p>';
				}
			}
		}
		?>
		<div class="notice notice-error is-dismissible mfm-settings-notice">
			<p><?php echo esc_html__( 'Settings update fail, please check provided values', 'website-file-changes-monitor' ); ?></p>
			<?php echo wp_kses_post( $contextual_messages ); ?>
		</div>
		<?php
	}

	/**
	 * Show a notice in admin to alert user to recently found changes.
	 *
	 * @return void
	 *
	 * @since 2.0.0
	 */
	public static function changes_available_notice() {
		$our_pages = array(
			'file-monitor-admin',
			'file-monitor-settings',
			'file-monitor-help',
		);

		if ( isset( $_GET['page'] ) && in_array( sanitize_key( wp_unslash( $_GET['page'] ) ), $our_pages, true ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$has_permission = ( current_user_can( 'install_plugins' ) || current_user_can( 'activate_plugins' ) || current_user_can( 'delete_plugins' ) || current_user_can( 'update_plugins' ) || current_user_can( 'install_themes' ) );
		if ( ! $has_permission ) {
			return;
		}

		$needed = get_site_option( MFM_PREFIX . 'event_notification_dismissed', false );

		if ( $needed ) {
			return;
		}

		$events_count = ( ceil( DB_Handler::get_events_count() ) > 99 ) ? '99+' : ceil( DB_Handler::get_events_count() );
		$suffix       = ( $events_count > 1 ) ? esc_html__( 'file changes.', 'website-file-changes-monitor' ) : esc_html__( 'file change.', 'website-file-changes-monitor' );
		$label        = ( $events_count > 1 ) ? esc_html__( 'View changes', 'website-file-changes-monitor' ) : esc_html__( 'View change', 'website-file-changes-monitor' );

		?>
		<div class="notice notice-success is-dismissible mfm-events-notice" data-dismiss-nonce="<?php echo esc_attr( wp_create_nonce( MFM_PREFIX . 'dismiss_notice_nonce' ) ); ?>">
			<p><?php echo esc_html__( 'Melapress File Monitor has detected', 'website-file-changes-monitor' ); ?> <?php echo esc_attr( $events_count ); ?>  <?php echo esc_attr( $suffix ); ?></p>
			<p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'admin.php?page=file-monitor-admin' ) ); ?>"><?php echo esc_attr( $label ); ?></a></p>
		</div>
		<script type='text/javascript'>
		jQuery('body').on('click', '.mfm-events-notice button', function(e) {
			e.preventDefault();
			var eventNonce = jQuery( '[data-dismiss-nonce]' ).attr('data-dismiss-nonce');	
			jQuery.ajax({
				url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'mfm_dismiss_events_notice',
					nonce: eventNonce,
				},
				complete: function(data) {
					// Nil..
				},
			});
		});
		</script>
		<?php
	}

	/**
	 * Add shortcut links to plugins page.
	 *
	 * @param array $old_links - Array of old links.
	 *
	 * @return array
	 *
	 * @since 2.0.0
	 */
	public static function shortcut_links( $old_links ) {
		$new_links[] = '<a href="' . add_query_arg( 'page', 'file-monitor-admin', admin_url( 'admin.php' ) ) . '">' . __( 'See File Changes', 'website-file-changes-monitor' ) . '</a>';
		$new_links[] = '<a href="' . add_query_arg( 'page', 'file-monitor-settings', admin_url( 'admin.php' ) ) . '">' . __( 'Settings', 'website-file-changes-monitor' ) . '</a>';
		$new_links[] = '<a href="' . add_query_arg( 'page', 'file-monitor-help', admin_url( 'admin.php' ) ) . '">' . __( 'Support', 'website-file-changes-monitor' ) . '</a>';
		return array_merge( $new_links, $old_links );
	}

	/**
	 * Show notice to recently updated plugin.
	 *
	 * @return void
	 *
	 * @since 2.2.0
	 */
	public static function plugin_was_updated_banner() {
		$show_update_notice = get_site_option( MFM_PREFIX . 'update_notice_needed', false );
		$screen             = get_current_screen();
		$pages_for_banner   = array(
			'toplevel_page_file-monitor-admin',
			'toplevel_page_file-monitor-admin-network',
		);

		if ( in_array( $screen->base, $pages_for_banner, true ) && $show_update_notice ) {
			?>
			<!-- Copy START -->
			<div class="mfm-plugin-update">
				<div class="mfm-plugin-update-content">
					<h2 class="mfm-plugin-update-title"><?php esc_html_e( 'Melapress File Monitor has been updated to version', 'website-file-changes-monitor' ); ?> <?php echo esc_attr( MFM_WP_VERSION ); ?>.</h2>
					<p class="mfm-plugin-update-text">
						<?php esc_html_e( 'You are now running the latest version of Melapress File Monitor. To see what\'s been included in this update, refer to the plugin\'s release notes and change log where we list all new features, updates, and bug fixes.', 'website-file-changes-monitor' ); ?>							
					</p>
					<a href="https://melapress.com/support/kb/website-file-changes-monitor-plugin-changelog/?utm_source=plugin&utm_medium=banner&utm_campaign=mfm" target="_blank" class="mfm-cta-link"><?php esc_html_e( 'Read the release notes', 'website-file-changes-monitor' ); ?></a>
				</div>
				<button aria-label="Close button" class="mfm-plugin-update-close" data-dismiss-nonce="<?php echo esc_attr( wp_create_nonce( MFM_PREFIX . 'dismiss_update_notice_nonce' ) ); ?>"></button>
			</div>
			<!-- Copy END -->
			
			<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready(function( $ ) {
				jQuery( 'body' ).on( 'click', '.mfm-plugin-update-close, .mfm-cta-link', function ( e ) {
					var nonce  = jQuery( '.mfm-plugin-update [data-dismiss-nonce]' ).attr( 'data-dismiss-nonce' );
					
					jQuery.ajax({
						type: 'POST',
						url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
						async: true,
						data: {
							action: 'dismiss_mfm_update_notice',
							nonce : nonce,
						},
						success: function ( result ) {		
							jQuery( '.mfm-plugin-update' ).slideUp( 300 );
						}
					});
				});
			});
			//]]>
			</script>
			<?php
		}

		if ( ( in_array( $screen->base, $pages_for_banner, true ) && $show_update_notice ) ) {
			?>
			<style type="text/css">
				/* Melapress brand font 'Quicksand' — There maybe be a preferable way to add this but this seemed the most discrete. */
				@font-face {
					font-family: 'Quicksand';
					src: url('<?php echo \esc_url( MFM_WP_URL ); ?>assets/fonts/Quicksand-VariableFont_wght.woff2') format('woff2');
					font-weight: 100 900; /* This indicates that the variable font supports weights from 100 to 900 */
					font-style: normal;
				}
				
				.mfm-plugin-update, .mfm-plugin-data-migration {
					background-color: #1A3060;
					border-radius: 7px;
					color: #fff;
					display: flex;
					justify-content: space-between;
					align-items: center;
					padding: 1.66rem;
					position: relative;
					overflow: hidden;
					transition: all 0.2s ease-in-out;
					margin-top: 20px;
					margin-right: 20px;
				}
			

				.mfm-plugin-update-content {
					max-width: 45%;
				}
				
				.mfm-plugin-update-title {
					margin: 0;
					font-size: 20px;
					font-weight: bold;
					font-family: Quicksand, sans-serif;
					line-height: 1.44rem;
					color: #fff;
				}
				
				.mfm-plugin-update-text {
					margin: .25rem 0 0;
					font-size: 0.875rem;
					line-height: 1.3125rem;
				}
				
				.mfm-plugin-update-text a:link {
					color: #FF8977;
				}
				
				.mfm-cta-link {
					border-radius: 0.25rem;
					background: #FF8977;
					color: #0000EE;
					font-weight: bold;
					text-decoration: none;
					font-size: 0.875rem;
					padding: 0.675rem 1.3rem .7rem 1.3rem;
					transition: all 0.2s ease-in-out;
					display: inline-block;
					margin: .5rem auto;
				}
				
				.mfm-cta-link:hover {
					background: #0000EE;
					color: #FF8977;
				}
				
				.mfm-plugin-update-close {
					background-image: url(<?php echo esc_url( MFM_WP_URL ) . 'assets/img/close-icon-rev.svg'; ?>); /* Path to your close icon */
					background-size: cover;
					width: 18px;
					height: 18px;
					border: none;
					cursor: pointer;
					position: absolute;
					top: 20px;
					right: 20px;
					background-color: transparent;
				}
				
				.mfm-plugin-update::before {
					content: '';
					background-image: url(<?php echo esc_url( MFM_WP_URL ) . 'assets/img/mfm-updated-bg.png'; ?>); /* Background image only displayed on desktop */
					background-size: 100%;
					background-repeat: no-repeat;
					background-position: 100% 51%;
					position: absolute;
					top: 0;
					right: 0;
					bottom: 0;
					left: 0;
					z-index: 0;
				}
				
				.mfm-plugin-update-content, .mfm-plugin-update-close {
					z-index: 1;
				}
				
				@media (max-width: 1200px) {
					.mfm-plugin-update::before {
						display: none;
					}
				
					.mfm-plugin-update-content {
						max-width: 100%;
					}
				}

				.mfm-plugin-data-migration {
					background-color: #D9E4FD;						
				}

				.mfm-plugin-data-migration * {
					color: #1A3060;
				}

				.mfm-plugin-data-migration .mfm-plugin-update-content {
					min-height: 80px;
				}
					
				#spinning-wrapper {
					position: absolute;
					right: -20px;
					height: 300px;
					width: 300px;
				}

				#spinning-wrapper .dashicons {
					height: 300px;
					height: 300px;
					font-size: 300px;
				}

				#spinning-wrapper  * {
					color: #8AAAF1 !important;
				}

				#spinning-wrapper.active {
					-webkit-animation: spin 4s infinite linear;
				}

				@-webkit-keyframes spin {
					0%  {-webkit-transform: rotate(0deg);}
					100% {-webkit-transform: rotate(360deg);}   
				}
			</style>
			<?php
		}
	}

	/**
	 * Handle notice dismissal.
	 *
	 * @return void
	 *
	 * @since 2.2.0
	 */
	public static function dismiss_update_notice() {
		// Grab POSTed data.
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : false;

		// Check nonce.
		if ( ! current_user_can( 'manage_options' ) || empty( $nonce ) || ! $nonce || ! wp_verify_nonce( $nonce, MFM_PREFIX . 'dismiss_update_notice_nonce' ) ) {
			wp_send_json_error( esc_html__( 'Nonce Verification Failed.', 'melapress-login-security' ) );
		}

		delete_site_option( MFM_PREFIX . 'update_notice_needed' );

		wp_send_json_success( esc_html__( 'Complete.', 'melapress-login-security' ) );
	}

	/**
	 * Simple checker for admin facing notices.
	 *
	 * @return int - Count.
	 *
	 * @since 2.2.0
	 */
	public static function get_current_notices_count() {
		$count = 0;

		if ( get_site_option( MFM_PREFIX . 'update_notice_needed', false ) ) {
			++$count;
		}

		return $count;
	}

	/**
	 * Show notice to user on plugin version update.
	 *
	 * @return void
	 *
	 * @since 2.2.0
	 */
	public static function check_plugin_update() {
		$stored_version          = get_site_option( MFM_PREFIX . 'active_version', false );
		$existing_last_scan_time = get_site_option( MFM_PREFIX . 'last_scan_time', false );

		if ( $existing_last_scan_time && ! empty( $existing_last_scan_time ) ) {
			if ( ! empty( $stored_version ) && version_compare( $stored_version, MFM_WP_VERSION, '<' ) ) {
				update_site_option( MFM_PREFIX . 'active_version', MFM_WP_VERSION );
				update_site_option( MFM_PREFIX . 'show_update_notice', true );
			} elseif ( empty( $stored_version ) ) {
				update_site_option( MFM_PREFIX . 'active_version', MFM_WP_VERSION );
				update_site_option( MFM_PREFIX . 'show_update_notice', true );
			}

			if ( get_site_option( MFM_PREFIX . 'show_update_notice', false ) ) {
				delete_site_option( MFM_PREFIX . 'show_update_notice' );
				update_site_option( MFM_PREFIX . 'update_notice_needed', true );
				$args = array(
					'page' => 'file-monitor-admin',
				);
				$url  = add_query_arg( $args, network_admin_url( 'admin.php' ) );
				wp_safe_redirect( $url );
				exit;
			}
		}

		if ( ! $stored_version ) {
			update_site_option( MFM_PREFIX . 'active_version', MFM_WP_VERSION );
		}

		if ( get_site_option( MFM_PREFIX . 'show_update_notice', false ) ) {
			delete_site_option( MFM_PREFIX . 'show_update_notice' );
			update_site_option( MFM_PREFIX . 'update_notice_needed', true );
			$args = array(
				'page' => 'file-monitor-admin',
			);
			$url  = add_query_arg( $args, network_admin_url( 'admin.php' ) );
			wp_safe_redirect( $url );
			exit;
		}
	}
}
