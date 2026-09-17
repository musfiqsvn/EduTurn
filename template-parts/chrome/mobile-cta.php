<?php
/**
 * EduTurn — Mobile sticky quick-action bar.
 */
if ( ! apply_filters( 'uturn_mobile_cta', true ) ) {
	return;
}
?><nav id="mobile-cta" class="mobile-cta" aria-label="Quick actions"><a href="<?php echo esc_url( uturn_url( 'apply' ) ); ?>" class="primary"><?php echo uturn_icon( 'cap' ); ?><span><?php echo esc_html( uturn_t( 'ভর্তি', 'Apply' ) ); ?></span></a><a href="<?php echo esc_url( uturn_url( 'results' ) ); ?>"><?php echo uturn_icon( 'result' ); ?><span><?php echo esc_html( uturn_t( 'ফলাফল', 'Results' ) ); ?></span></a><a href="tel:<?php echo esc_attr( uturn_opt( 'phone_href' ) ); ?>"><?php echo uturn_icon( 'phone' ); ?><span><?php echo esc_html( uturn_t( 'কল করুন', 'Call' ) ); ?></span></a></nav>
