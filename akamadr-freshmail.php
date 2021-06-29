<?php
/**
 * Plugin Name: Akamadr - Freshmail
 * Plugin URI: https://akamadr.pl
 * Description: Freshmail integration
 * Version: 1.0.1
 * Author: Akamadr
 * Author URI: https://akamadr.pl
 * Text Domain: woocommerce-size-guide
 */


if ( ! class_exists( 'Akamadr_Freshmail' ) ) {

    class Akamadr_Freshmail {

        public function __construct() {
            add_shortcode( 'freshmail_input', array( $this, 'add_shortcode' ) );

            add_action( 'wp_ajax_af_submit_subscriber',  array( $this, 'submit_subscriber' ) );
            add_action( 'wp_ajax_nopriv_af_submit_subscriber', array( $this, 'submit_subscriber' ) );

            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

            add_action( 'wp_footer', array( $this, 'add_popup' ) );
            

            //add_action( 'af_subscriber_added', array( $this, 'send_email' ), 10, 2 );
        }

        public function enqueue_scripts() {
            wp_register_script( 'af-scripts', plugin_dir_url( __FILE__ ) . 'assets/js/akamadr-freshmail.js', array( 'jquery' ), '1.0.0', true );

            wp_enqueue_style( 'af-style', plugin_dir_url( __FILE__ ) . 'assets/css/akamadr-freshmail.css', '1.0.0' );

            wp_localize_script( 'af-scripts', 'af_object', array(
                'ajaxurl' => admin_url( 'admin-ajax.php' )
            ) );
        }

        public function add_subscriber( $email_address ) {
            require 'src/FreshMail/RestApi.php';
            require 'src/FreshMail/RestException.php';
            require 'config.php';
        
        
            $rest = new \FreshMail\RestApi();
        
            $rest->setApiKey(FM_API_KEY);
            $rest->setApiSecret(FM_API_SECRET);
        
            $data = [
                'email' => $email_address,
                'list'  => 'smsudd46nk',
            ];

            $response = false;
        
            try {
                $response = $rest->doRequest('subscriber/add', $data);

                //do_action( 'af_subscriber_added', $email_address, $response );
            } catch (Exception $e) {
                //do_action( 'af_failed', $email_address, $e );
                //echo 'Error message: ' . $e->getMessage() . ', Error code: ' . $e->getCode() . ', HTTP code: ' . $rest->getHttpCode() . PHP_EOL;
            }

            return $response;
            
        }

        public function submit_subscriber() {
            $error = 'Błąd';

            if ( isset( $_POST['freshmail_email'] ) && ! empty( $_POST['freshmail_email'] ) ) {
                $email_address = sanitize_email( $_POST['freshmail_email'] );

                $response = $this->add_subscriber( $email_address );

                if ( isset( $response['status'] ) && 'OK' === $response['status'] ) {

                    ob_start();

                    include 'templates/popup-success.php';

                    $popup = ob_get_clean();

                    wp_send_json_success( array(
                        'popup' => $popup,
                        'message' => __( 'Adres dodany poprawnie', 'akamadr-freshmail' )
                    ) );
                } else {
                    $error = __( 'Adres nieprawidłowy lub znajduje się już w bazie', 'akamadr-freshmail' );
                }
            }

            wp_send_json_error( array(
                'message' => $error
            ) );
        }

        public function add_shortcode( $atts ) {
            wp_enqueue_script( 'af-scripts' );

            ob_start();
            ?>
            <form class="form-inline mb-0 js-af-form" method="post"><br />
                <input type="hidden" name="subscribers_list_hash" value="smsudd46nk" /><br />
                <input type="text" id="freshmail_email" class="form-control js-af-mail" placeholder="Twój adres e-mail" name="freshmail_email"/> <br/><br />
                <div class="freshmail_submit">
                    <input type="submit" value="Zapisuję się" class="btn btn--gradient" />
                    
                    <div class="spinner">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                    
                </div>
            </form>
            <div class="newsletter__info">Od czasu do czasu prześlemy Ci porady z branży marketingowej, informacje o ciekawych wydarzeniach, ofertach i nowych usługach Sempai.</div>
            <div class="newsletter__policy"><a href="https://sempai.pl/polityka-prywatnosci/" target="_blank">Polityka prywatności</a></div>
            <div class="newsletter-notyfication"></div>
            <?php
            $form = ob_get_clean();
        
            return $form;
        }

        public function send_email( $email, $response ) {
            if ( isset( $response['status'] ) && 'OK' === $response['status'] ) {
                //wp_mail( $email )
            }
        }


        public function add_popup() {
            echo '<div id="newsletter__succes"></div>';
        }
        
    }

    new Akamadr_Freshmail();
}