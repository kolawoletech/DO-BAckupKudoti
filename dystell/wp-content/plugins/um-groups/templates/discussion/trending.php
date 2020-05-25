<?php if( !defined( 'ABSPATH' ) ) exit; ?>

<ul class="um-groups-trending">

	<?php foreach( ( array ) $hashtags as $hashtag ) { ?>

		<li><a href="<?php echo esc_url( add_query_arg( 'hashtag', $hashtag->slug, um_get_core_page( 'activity' ) ) ); ?>">#<?php echo esc_attr( $hashtag->name ); ?></a></li>

	<?php } ?>

</ul>