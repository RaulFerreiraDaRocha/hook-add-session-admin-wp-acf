 /*CODIGO ADICIONADO*/
            
			$_POST['log'] = $_POST['email'];
                $_POST['pwd'] = $_POST['password'];
                $_POST['wp-submit'] = 'Acessar';
                $_POST['testcookie'] = 1;
                $secure_cookie   = '';
		$customize_login = isset( $_REQUEST['customize-login'] );

		if ( $customize_login ) {
			wp_enqueue_script( 'customize-base' );
		}

		// If the user wants SSL but the session is not SSL, force a secure cookie.
		if ( ! empty( $_POST['log'] ) && ! force_ssl_admin() ) {
			$user_name = sanitize_user( wp_unslash( $_POST['log'] ) );
			$user      = get_user_by( 'login', $user_name );

			if ( ! $user && strpos( $user_name, '@' ) ) {
				$user = get_user_by( 'email', $user_name );
			}

			if ( $user ) {
				if ( get_user_option( 'use_ssl', $user->ID ) ) {
					$secure_cookie = true;
					force_ssl_admin( true );
				}
			}
		}

		if ( isset( $_REQUEST['redirect_to'] ) ) {
			$redirect_to = $_REQUEST['redirect_to'];
			// Redirect to HTTPS if user wants SSL.
			if ( $secure_cookie && false !== strpos( $redirect_to, 'wp-admin' ) ) {
				$redirect_to = preg_replace( '|^http://|', 'https://', $redirect_to );
			}
		} else {
			$redirect_to = admin_url();
		}

		$reauth = empty( $_REQUEST['reauth'] ) ? false : true;

		$user = wp_signon( array(), $secure_cookie );

		if ( empty( $_COOKIE[ LOGGED_IN_COOKIE ] ) ) {
			if ( headers_sent() ) {
				$user = new WP_Error(
					'test_cookie',
					sprintf(
						/* translators: 1: Browser cookie documentation URL, 2: Support forums URL. */
						__( '<strong>Error</strong>: Cookies are blocked due to unexpected output. For help, please see <a href="%1$s">this documentation</a> or try the <a href="%2$s">support forums</a>.' ),
						__( 'https://wordpress.org/support/article/cookies/' ),
						__( 'https://wordpress.org/support/forums/' )
					)
				);
			} elseif ( isset( $_POST['testcookie'] ) && empty( $_COOKIE[ TEST_COOKIE ] ) ) {
				// If cookies are disabled, we can't log in even with a valid user and password.
				$user = new WP_Error(
					'test_cookie',
					sprintf(
						/* translators: %s: Browser cookie documentation URL. */
						__( '<strong>Error</strong>: Cookies are blocked or not supported by your browser. You must <a href="%s">enable cookies</a> to use WordPress.' ),
						__( 'https://wordpress.org/support/article/cookies/#enable-cookies-in-your-browser' )
					)
				);
			}
		}

		/*FIM CODIGO ADICIONADO*/
