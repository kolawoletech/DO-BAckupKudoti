
<style>
	.vgca-iframe-wrapper iframe {
		width: 100%;
		min-width: 100%;
		max-width: 100%;
		height: 100%;
		position: absolute;
		border: 0px;
		/* Fix. Some themes add loading icons on top of the iframe due to lazy loading*/
		background-image: none !important;
		background: transparent !important;
	}
	.vgca-iframe-wrapper {
		width: 100%;
		min-width: 100%;
		max-width: 100%;
		height: 100%;
		position: relative;	
		min-height: 80px;
	}
	.lds-ring {
		display: inline-block;
		position: relative;
		width: 64px;
		height: 64px;
	}
	.lds-ring div {
		box-sizing: border-box;
		display: block;
		position: absolute;
		width: 51px;
		height: 51px;
		margin: 6px;
		border: 6px solid #fff;
		border-radius: 50%;
		animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
		border-color: #fff transparent transparent transparent;
	}
	.lds-ring div:nth-child(1) {
		animation-delay: -0.45s;
	}
	.lds-ring div:nth-child(2) {
		animation-delay: -0.3s;
	}
	.lds-ring div:nth-child(3) {
		animation-delay: -0.15s;
	}
	@keyframes lds-ring {
		0% {
			transform: rotate(0deg);
		}
		100% {
			transform: rotate(360deg);
		}
	}
	.vgca-loading-indicator {
		display: block;
		background: #00000030;
		position: absolute;
		z-index: 9;
		left: 49%;
		border-radius: 50%;
	}

	@media only screen and (max-width: 768px){
		.vgca-iframe-wrapper.desktop-as-mobile {
			overflow-x: scroll;
		}
		.vgca-iframe-wrapper.desktop-as-mobile iframe {
			width: 1300px !important;
			max-width: 1300px !important;
			min-width: 1300px !important;
		}
	}
	.vgca-iframe-wrapper iframe.vgfa-full-screen {
		position: fixed !important;
		top: 0 !important;
		left: 0 !important;
		height: 100% !important;
		width: 100% !important;
		/*Super high number to make sure it opens on top of the wp-admin bar*/
		z-index: 1000000000;
		/*The iframe shouldn't be transparent because if the admin page is transparent, it will look on top of the frontend header*/
		background-color: white;
	}
	.vgca-iframe-wrapper.vgfa-is-loading iframe {
		visibility: hidden;
	}
</style>
<div  class="vgfa-is-loading vgca-iframe-wrapper <?php if ($use_desktop_in_mobile) echo 'desktop-as-mobile'; ?>">
	<!--Loading indicator-->
	<div class="lds-ring vgca-loading-indicator"><div></div><div></div><div></div><div></div></div>
	<iframe id="vgca-iframe-<?php echo crc32($page_url); ?>" data-lazy-load="<?php echo (bool) $lazy_load; ?>" data-forward-parameters="<?php echo (bool) $forward_parameters; ?>"  data-src="<?php echo esc_url($final_url); ?>" src="<?php echo $lazy_load ? '' : esc_url($final_url); ?>"></iframe>
</div>