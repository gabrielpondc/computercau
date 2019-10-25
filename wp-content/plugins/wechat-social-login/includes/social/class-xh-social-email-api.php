<?php
if (! defined('ABSPATH')) {
    exit();
}

class XH_Social_Email_Api extends Abstract_XH_Social_Settings
{

    /**
     * Instance
     * 
     * @since 1.0.0
     */
    private static $_instance;

    /**
     * Instance
     * 
     * @since 1.0.0
     */
    public static function instance()
    {
        if (is_null(self::$_instance))
            self::$_instance = new self();
        return self::$_instance;
    }

    private function __construct()
    {
        $this->id = 'settings_default_other_email';
        $this->title = __('Email Settings', XH_SOCIAL);
        
        $this->init_form_fields();
    }

    public function init()
    {
        add_action('phpmailer_init', array($this,'phpmailer_init_smtp'), 999, 1);
        add_filter('wp_mail_from',array($this,'wp_mail_smtp_mail_from'),999,1);
        add_filter('wp_mail_from_name',array($this,'wp_mail_smtp_mail_from_name'),999,1);
    }
    
    public function wp_mail_smtp_mail_from_name ($orig) {
        // Only filter if the from name is the default
        
        //if ($orig == 'WordPress') {
            $name = $this->get_option('mail_from_name');
            if(!empty($name)&&is_string($name)){
                return $name;
            }
        //}
    
        // If in doubt, return the original value
        //return $orig;
    
    }
    public function wp_mail_smtp_mail_from ($orig) {
        // This is copied from pluggable.php lines 348-354 as at revision 10150
        // http://trac.wordpress.org/browser/branches/2.7/wp-includes/pluggable.php#L348
    
        // Get the site domain and get rid of www.
//         $sitename = strtolower( $_SERVER['SERVER_NAME'] );
//         if ( substr( $sitename, 0, 4 ) == 'www.' ) {
//             $sitename = substr( $sitename, 4 );
//         }
    
//         $default_from = 'wordpress@' . $sitename;        
//         if ( $orig != $default_from ) {
//             return $orig;
//         }
        
        $from_email = $this->get_option('mail_from');
        if (is_email($from_email)){
            return $from_email;
        }
        
        return $orig;
    
    }
    
    /**
     * 
     * @param PHPMailer $phpmailer
     * @return PHPMailer
     */
    public function phpmailer_init_smtp($phpmailer)
    {
        $mailer =$this->get_option('mailer','mail');
        if(empty($mailer)||$mailer=='mail'){
            return;
        }
        
        if($mailer==$phpmailer->Mailer){
            return;
        }

        if(!empty( $phpmailer->From)){
            $phpmailer->Sender = $phpmailer->From;
        }
        
        // Set the mailer type as per config above, this overrides the already called isMail method        
        switch ($mailer){
            case 'mail':
            default:
                return;
            case 'smtp':
                $phpmailer->Mailer = $mailer;
                $phpmailer->SMTPSecure = $this->get_option('smtp_ssl');
                // Set the other options
                $phpmailer->Host = $this->get_option('smtp_host');
                $phpmailer->Port = $this->get_option('smtp_port');
                $phpmailer->Timeout = 10;
                // If we're using smtp auth, set the username & password
                if ($this->get_option('smtp_auth') == "yes") {
                    $phpmailer->SMTPAuth = true;
                    $phpmailer->Username = $this->get_option('smtp_user');
                    $phpmailer->Password = $this->get_option('smtp_pass');
                }
                return;
        }
    }

    public function init_form_fields()
    {
        $this->form_fields = array(
            'disabled_captcha' => array(
                'title' => __('Disabled Captcha', XH_SOCIAL),
                'type' => 'checkbox',
                'label' => __('Disable captcha verify when send email validation code.', XH_SOCIAL),
                'default' => 'yes'
            ),
            'mail_from' => array(
                'title' => __('From Email', XH_SOCIAL),
                'type' => 'text',
                'description' => __('You can specify the email address that emails should be sent from. If you leave this blank, the default email will be used.', XH_SOCIAL)
            ),
            'mail_from_name' => array(
                'title' => __('From Name', XH_SOCIAL),
                'type' => 'text',
                'description' => __('You can specify the name that emails should be sent from. If you leave this blank, the emails will be sent from WordPress.', XH_SOCIAL)
            ),
            'mailer' => array(
                'title' => __('Mailer', XH_SOCIAL),
                'type' => 'section',
                'options' => array(
                    'mail' => __('Use the PHP mail() function to send emails(wordpress default).', XH_SOCIAL),
                    'smtp' => __('via SMTP.', XH_SOCIAL),
                ),
                'description' => __('You can specify the email address that emails should be sent from. If you leave this blank, the default email will be used.', XH_SOCIAL)
            ),
            'smtp_settings' => array(
                'title' => __('SMTP Options', XH_SOCIAL),
                'type' => 'subtitle',
                'tr_css' => 'section-mailer section-smtp'
            ),
            'smtp_host' => array(
                'title' => __('SMTP Host', XH_SOCIAL),
                'type' => 'text',
                'placeholder'=>'smtp.exmail.qq.com',
                'tr_css' => 'section-mailer section-smtp'
            ),
            'smtp_port' => array(
                'title' => __('SMTP Port', XH_SOCIAL),
                'type' => 'text',
                'placeholder'=>'',
                'tr_css' => 'section-mailer section-smtp'
            ),
            'smtp_ssl' => array(
                'title' => __('Encryption', XH_SOCIAL),
                'type' => 'select',
                'options' => array(
                    '' => __('No encryption.', XH_SOCIAL),
                    'ssl' => __('Use SSL encryption.', XH_SOCIAL),
                    'tls' => __('Use TLS encryption. This is not the same as STARTTLS. For most servers SSL is the recommended option.', XH_SOCIAL)
                ),
                'tr_css' => 'section-mailer section-smtp'
            ),
            'smtp_accounts' => array(
                'title' => __('SMTP account', XH_SOCIAL),
                'type' => 'subtitle',
                'tr_css' => 'section-mailer section-smtp'
            ),
            'smtp_auth' => array(
                'title' => __('Authentication', XH_SOCIAL),
                'type' => 'select',
                'options' => array(
                    'no' => __('No: Do not use SMTP authentication.', XH_SOCIAL),
                    'yes' => __('Yes: Use SMTP authentication.', XH_SOCIAL)
                ),
                'tr_css' => 'section-mailer section-smtp',
                'description' => __('If "Authentication" set to no, the values below are ignored.', XH_SOCIAL)
            ),
            'smtp_user' => array(
                'title' => __('Username', XH_SOCIAL),
                'type' => 'text',
                'tr_css' => 'section-mailer section-smtp'
            ),
            'smtp_pass' => array(
                'title' => __('Password', XH_SOCIAL),
                'type' => 'text',
                'tr_css' => 'section-mailer section-smtp'
            )
        );
    }
   
    	function swpsmtp_test_mail( $to_email, $subject, $message ) {
    		$ret = array();
    		
    		require_once( ABSPATH . WPINC . '/class-phpmailer.php' );
    		$mail = new PHPMailer( true );
    		
    		try {
    			
    			$charset	 = get_bloginfo( 'charset' );
    			$mail->CharSet	 = $charset;
    			
    			$from_name	 = $this->get_option('mail_from_name');
    			$from_email	 = $this->get_option('mail_from');
    			$mail->IsSMTP();
    			
    			// send plain text test email
    			$mail->ContentType = 'text/plain';
    			$mail->IsHTML( false );
    			
    			/* If using smtp auth, set the username & password */
    			if ( 'yes' == $this->get_option('smtp_auth') ) {
    				$mail->SMTPAuth	 = true;
    				$mail->Username	 = $this->get_option('smtp_user');
    				$mail->Password	 = $this->get_option('smtp_pass');
    			}
    			
    			/* Set the SMTPSecure value, if set to none, leave this blank */
    			$ssl = $this->get_option('smtp_ssl');
    			if ( $ssl ) {
    				$mail->SMTPSecure = $ssl;
    			}
    			
    			/* PHPMailer 5.2.10 introduced this option. However, this might cause issues if the server is advertising TLS with an invalid certificate. */
    			$mail->SMTPAutoTLS = false;
    			
    			if ($ssl=='ssl' ) {
    				// Insecure SSL option enabled
    				$mail->SMTPOptions = array(
    						'ssl' => array(
    								'verify_peer'		 => false,
    								'verify_peer_name'	 => false,
    								'allow_self_signed'	 => true
    						) );
    			}
    			
    			/* Set the other options */
    			$mail->Host	 = $this->get_option('smtp_host');
    			$mail->Port	 =  $this->get_option('smtp_port');
    			
    			$mail->SetFrom( $from_email, $from_name );
    			//This should set Return-Path header for servers that are not properly handling it, but needs testing first
    			//$mail->Sender		 = $mail->From;
    			$mail->Subject		 = $subject;
    			$mail->Body		 = $message;
    			$mail->AddAddress( $to_email );
    			global $debugMSG;
    			$debugMSG		 = '';
    			$mail->Debugoutput	 = function($str, $level) {
    				global $debugMSG;
    				$debugMSG .= $str;
    			};
    			$mail->SMTPDebug = 1;
    			
    			/* Send mail and return result */
    			$mail->Send();
    			$mail->ClearAddresses();
    			$mail->ClearAllRecipients();
    		} catch ( Exception $e ) {
    			$ret[ 'error' ] = $mail->ErrorInfo;
    		}
    		
    		$ret[ 'debug_log' ] = $debugMSG;
    		
    		return $ret;
    	}
    	
    	function swpsmtp_sanitize_textarea($str) {
    		if (function_exists ( 'sanitize_textarea_field' )) {
    			return sanitize_textarea_field ( $str );
    		}
    		$filtered = wp_check_invalid_utf8 ( $str );
    		
    		if (strpos ( $filtered, '<' ) !== false) {
    			$filtered = wp_pre_kses_less_than ( $filtered );
    			// This will strip extra whitespace for us.
    			$filtered = wp_strip_all_tags ( $filtered, false );
    			
    			// Use html entities in a special case to make sure no later
    			// newline stripping stage could lead to a functional tag
    			$filtered = str_replace ( "<\n", "&lt;\n", $filtered );
    		}
    		
    		$filtered = trim ( $filtered );
    		
    		$found = false;
    		while ( preg_match ( '/%[a-f0-9]{2}/i', $filtered, $match ) ) {
    			$filtered = str_replace ( $match [0], '', $filtered );
    			$found = true;
    		}
    		
    		if ($found) {
    			// Strip out the whitespace that may now exist after removing the octets.
    			$filtered = trim ( preg_replace ( '/ +/', ' ', $filtered ) );
    		}
    		
    		return $filtered;
    	}
    public function admin_form_end(){
    	parent::admin_form_end();
    	ini_set('display_errors', 'On');
    	error_reporting(E_ALL);
    	?>
    	<div class="swpsmtp-tab-container" data-tab-name="testemail">
    	    <div class="postbox" style="padding:15px;">
    		<h3 class="hndle"><label for="title"><?php _e( 'Test Email', XH_SOCIAL ); ?></label></h3>
    		<div class="inside">
    	
				<?php
				$swpsmtp_to = '';
				$smtp_test_mail=array();
				$smtp_test_mail ['swpsmtp_to'] = '';
				$smtp_test_mail ['swpsmtp_subject'] = '';
				$smtp_test_mail ['swpsmtp_message'] = '';
				$error='';
				if (isset ( $_POST ['swpsmtp_test_submit'] ) && check_admin_referer ( plugin_basename ( __FILE__ ), 'xh_social_swpsmtp_test_nonce_name' )) {
					if (isset ( $_POST ['swpsmtp_to'] )) {
						$to_email = sanitize_text_field ( $_POST ['swpsmtp_to'] );
						if (is_email ( $to_email )) {
							$swpsmtp_to = $to_email;
						} else {
							$error .= __ ( "Please enter a valid email address in the recipient email field.", XH_SOCIAL );
						}
					}
					$swpsmtp_subject = isset ( $_POST ['swpsmtp_subject'] ) ? sanitize_text_field ( $_POST ['swpsmtp_subject'] ) : '';
					$swpsmtp_message = isset ( $_POST ['swpsmtp_message'] ) ? $this->swpsmtp_sanitize_textarea ( $_POST ['swpsmtp_message'] ) : '';
					
					// Save the test mail details so it doesn't need to be filled in everytime.
					$smtp_test_mail=array();
					$smtp_test_mail ['swpsmtp_to'] = $swpsmtp_to;
					$smtp_test_mail ['swpsmtp_subject'] = $swpsmtp_subject;
					$smtp_test_mail ['swpsmtp_message'] = $swpsmtp_message;
					update_option ( 'smtp_test_mail', $smtp_test_mail );
					
					if (! empty ( $swpsmtp_to )) {
					    
						$test_res = $this->swpsmtp_test_mail ( $swpsmtp_to, $swpsmtp_subject, $swpsmtp_message );
					}
				}
				
				if ( isset( $test_res ) && is_array( $test_res ) ) {
				    if ( isset( $test_res[ 'error' ] ) ) {
					$errmsg_class	 = ' msg-error';
					$errmsg_text	 = '<b>' . __( 'Following error occured when attempting to send test email:', XH_SOCIAL ) . '</b><br />' . $test_res[ 'error' ];
				    } else {
					$errmsg_class	 = ' msg-success';
					$errmsg_text	 = '<b>' . __( 'Test email was successfully sent. No errors occured during the process.', XH_SOCIAL ) . '</b>';
				    }
				    ?>
	
				    <div class="swpsmtp-msg-cont<?php echo $errmsg_class; ?>">
					<?php echo $errmsg_text; ?>
	
					<?php
					if ( isset( $test_res[ 'debug_log' ] ) ) {
					    ?>
		    			<br /><br />
		    			<a id="swpsmtp-show-hide-log-btn" href="#0"><?php _e( 'Show Debug Log', XH_SOCIAL ); ?></a>
		    			<p id="swpsmtp-debug-log-cont"><textarea rows="20" style="width: 100%;"><?php echo $test_res[ 'debug_log' ]; ?></textarea></p>
		    			<script>
		    			    jQuery(function ($) {
		    				$('#swpsmtp-show-hide-log-btn').click(function (e) {
		    				    e.preventDefault();
		    				    var logCont = $('#swpsmtp-debug-log-cont');
		    				    if (logCont.is(':visible')) {
		    					$(this).html('<?php echo esc_attr( __( 'Show Debug Log', XH_SOCIAL ) ); ?>');
		    				    } else {
		    					$(this).html('<?php echo esc_attr( __( 'Hide Debug Log', XH_SOCIAL ) ); ?>');
		    				    }
		    				    logCont.toggle();
		    				});
						    <?php if ( isset( $test_res[ 'error' ] ) ) {?>
								$('#swpsmtp-show-hide-log-btn').click();
						    <?php } ?>
		    			    });
		    			</script>
					    <?php
					}
					?>
				    </div>
				    <?php
				}
				?>

    		    <form id="swpsmtp_settings_test_email_form" method="post" action="">
    			<table class="form-table">
    			    <tr valign="top">
    				<th scope="row"><?php _e( "To", XH_SOCIAL ); ?>:</th>
    				<td>
    				    <input id="swpsmtp_to" type="text" class="ignore-change" name="swpsmtp_to" value="<?php echo esc_html( $smtp_test_mail[ 'swpsmtp_to' ] ); ?>" /><br />
    				    <p class="description"><?php _e( "Enter the recipient's email address", XH_SOCIAL ); ?></p>
    				</td>
    			    </tr>
    			    <tr valign="top">
    				<th scope="row"><?php _e( "Subject", XH_SOCIAL ); ?>:</th>
    				<td>
    				    <input id="swpsmtp_subject" type="text" class="ignore-change" name="swpsmtp_subject" value="<?php echo esc_html( $smtp_test_mail[ 'swpsmtp_subject' ] ); ?>" /><br />
    				    <p class="description"><?php _e( "Enter a subject for your message", XH_SOCIAL ); ?></p>
    				</td>
    			    </tr>
    			    <tr valign="top">
    				<th scope="row"><?php _e( "Message", XH_SOCIAL ); ?>:</th>
    				<td>
    				    <textarea name="swpsmtp_message" id="swpsmtp_message" rows="5"><?php echo stripslashes( esc_textarea( $smtp_test_mail[ 'swpsmtp_message' ] ) ); ?></textarea><br />
    				    <p class="description"><?php _e( "Write your email message", XH_SOCIAL ); ?></p>
    				</td>
    			    </tr>
    			</table>
    			<p class="submit">
    			    <input type="submit" id="test-email-form-submit" class="button-primary" value="<?php _e( 'Send Test Email', XH_SOCIAL ) ?>" />
    			    <input type="hidden" name="swpsmtp_test_submit" value="submit" />
				<?php wp_nonce_field( plugin_basename( __FILE__ ), 'xh_social_swpsmtp_test_nonce_name' ); ?>
    			</p>
    		    </form>
    		</div><!-- end of inside -->
    	    </div><!-- end of postbox -->

    	</div>
    	<?php 
    }
}