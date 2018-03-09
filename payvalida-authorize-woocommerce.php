
<?php
class wc_PayValida extends WC_Payment_Gateway {
    function __construct() {
        // The global ID for this Payment method
        $this->id = "wc_payvalida";
        // The Title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
        $this->method_title = __( "Payvalida", 'wc_payvalida' );
				// Show Icon
				$this->icon	= apply_filters('woocomerce_payvalida_icon', plugins_url('/assets/payvalida.png', __FILE__));
        // The description for this Payment Gateway, shown on the actual Payment options page on the backend
        $this->method_description = __( "Pague con tarjeta de crédito con Payvalida Gateway (solo Colombia)", 'wc_payvalida' );
        // The title to be used for the vertical tabs that can be ordered top to bottom
        $this->title = __( "Payvalida", 'wc_payvalida' );
        // Bool. Can be set to true if you want payment fields to show on the checkout
        // if doing a direct integration, which we are doing in this case
        $this->has_fields = true;
        // Supports the default credit card form
        $this->supports = array( 'default_credit_card_form' );
        // This basically defines your settings which are then loaded with init_settings()
        $this->init_form_fields();
        // After init_settings() is called, you can get the settings and load them into variables, e.g:
        // $this->title = $this->get_option( 'title' );
        $this->init_settings();
        // Turn these settings into variables we can use
        foreach ( $this->settings as $setting_key => $value ) {
            $this->$setting_key = $value;
        }
        add_filter( 'woocommerce_credit_card_form_fields', array($this, 'credit_card_form_fields'), 10, 2 );
        // Save settings
        if ( is_admin() ) {
            // Versions over 2.0
            // Save our administration options. Since we are not going to be doing anything special
            // we have not defined 'process_admin_options' in this class so the method in the parent
            // class will be used instead
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        }
    } // End __construct()
        // define the woocommerce_credit_card_form_fields callback
    function credit_card_form_fields( $default_fields, $id ) {
        // make filter magic happen here...
        $extraInicio = array();
				$extraFin = array();
        $extra['card-tipodocumento-field'] =  '<p class="form-row form-row-wide">
         <label for="' . esc_attr( $id) . '-card-type">' . __( 'Tipo de documento', 'woocommerce' ) . ' <span class="required">*</span></label>
         <select class="wc-credit-card-form-card-type" name="' . ( esc_attr( $this->id ) ? esc_attr( $this->id ) . '-card-tipodocumento' : '' ) . '" id="' . esc_attr( $this->id ) . '-card-tipodocumento">
         <option value="CC">Cédula de Ciudadanía</option>
         <option value="CE">Cédula de Extranjería</option>
         <option value="TI">Tarjeta de Identidad</option>
         <option value="PA">Pasaporte</option>
         </select>
         </p>';
        $extra['card-documento-field'] = '<p class="form-row form-row-wide">
            <label for="' . esc_attr( $id ) . '-card-documento">' . __( 'Documento de identidad', 'wc_payvalida') . ' <span class="required">*</span></label>
            <input id="' . esc_attr( $id ) . '-card-documento" class="input-text wc-credit-card-form-card-documento" type="text" maxlength="15" autocomplete="off" placeholder="" name="' . $this->id . '-card-documento' . '" style="font-size: 1.5em; padding: 8px;" />
        </p>';
				$extraFin['card-cuota-field'] = '<p class="form-row form-row-wide">
            <label for="' . esc_attr( $id ) . '-card-cuota">' . __( 'Número de cuotas', 'wc_payvalida') . ' <span class="required">*</span></label>
            <input id="' . esc_attr( $id ) . '-card-cuota" class="input-text wc-credit-card-form-card-cuota" type="number" maxlength="2" min="1" max="48" step="1" autocomplete="off" placeholder="" name="' . $this->id . '-card-cuota' . '" style="font-size: 1.5em; padding: 8px;" />
        </p>';
				return array_merge($extra, $default_fields, $extraFin);
    }
    // add the filter
    // Build the administration fields for this specific Gateway
		public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'		=> __( 'Habilitar / Deshabilitar', 'wc_payvalida' ),
				'label'		=> __( 'Habilite esta pasarela de pago', 'wc_payvalida' ),
				'type'		=> 'checkbox',
				'default'	=> 'no',
			),
			'description' => array(
				'title'		=> __( 'Descripción', 'wc_payvalida' ),
				'type'		=> 'textarea',
				'desc_tip'	=> __( 'Título del pago del proceso de pago.', 'wc_payvalida' ),
				'default'	=> __( 'Comienza hoy mismo a vender tus productos o servicios en línea y tus clientes usar el medio de pago que prefieran.', 'wc_payvalida' ),
				'css'		=> 'max-width:450px;'
			),
			'api_login' => array(
				'title'		=> __( 'Payvalida merchant ID', 'wc_payvalida' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Esta es la identificación proporcionada por Payvalida cuando creas una cuenta', 'wc_payvalida' ),
			),
			'api_iva' => array(
				'title'		=> __( 'Merchant IVA', 'wc_payvalida' ),
				'type'		=> 'text',
				'default'	=> __( '0', 'wc_payvalida' ),
				'desc_tip'	=> __( 'Esta es la identificación proporcionada por Payvalida cuando creas una cuenta', 'wc_payvalida' ),
			),
			'fixed_hash' => array(
				'title'		=> __( 'Payvalida FIXED_HASH', 'wc_payvalida' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Este es el FIXED_HASH proporcionado por Payvalida cuando creas una cuenta.', 'wc_payvalida' ),
			),
			'fixed_hash_test' => array(
				'title'		=> __( 'Payvalida FIXED_HASH_TEST', 'wc_payvalida' ),
				'type'		=> 'text',
				'desc_tip'	=> __( 'Este es el FIXED_HASH de ambiente de pruebas proporcionado por Payvalida cuando creas una cuenta.', 'wc_payvalida' ),
			),
			'environment' => array(
				'title'		=> __( 'Modo de prueba Payvalida', 'wc_payvalida' ),
				'label'		=> __( 'Habilitar modo de prueba', 'wc_payvalida' ),
				'type'		=> 'checkbox',
				'description' => __( 'Este es el modo de prueba de Payvalida.', 'wc_payvalida' ),
				'default'	=> 'no',
			)
		);
	}
    /**
     * [card_to_type description]
     * @param  [type] $card_number [description]
     * @return [type]              [description]
     */
    protected function card_to_type($card_number, $default = 'UNKNOWN')
    {
        $firstNum = substr($card_number, 0, 1);
        if($firstNum == 3){
            return 'amex';
        }
        if($firstNum == 4){
            return 'visa';
        }
        if($firstNum == 5){
            return 'mc';
        }
        return $default;
    }
		/**
     * [card_to_number description]
     * @param  [type] $card_number [description]
     * @return [numer]              [number]
     */
    protected function card_to_number($card_number, $default = 0)
    {
        $firstNum = substr($card_number, 0, 1);
        if($firstNum == 3){
            return 40;
        }
        if($firstNum == 4){
            return 20;
        }
        if($firstNum == 5){
            return 30;
        }
        return $default;
    }
    // Submit payment and handle response
    public function process_payment( $order_id ) {
        global $woocommerce;
        // Get this Order's information so that we know
        // who to charge and how much
        $customer_order = new WC_Order( $order_id );
				// checking for transiction
				$environment = ( $this->environment == "yes" ) ? 'TRUE' : 'FALSE';
				// Decide which URL to post to
				$environment_transactions = ( "FALSE" == $environment )
                               ? 'https://ws-prod.payvalida.com/api/v2/tctransactions/'
						   : 'https://sandbox.payvalida.com/api/v2/tctransactions';
				$environment_orders = ( "FALSE" == $environment )
			                                ? 'https://ws-prod.payvalida.com/api/v2/porders/'
			 						   : 'https://sandbox.payvalida.com/api/v2/porders/';
				$fished = ( "FALSE" == $environment )
															 ?  $this->fixed_hash
							 :  $this->fixed_hash_test;
        //build up our email
        //$amount = number_format($customer_order->order_total, 2);
        $name = $customer_order->billing_first_name . ' ' . $customer_order->billing_last_name;
        $amount = $customer_order->order_total;
        $amount = $customer_order->order_total;
        $receiptid = $customer_order->get_order_number();
        $card_number = $this->input_post('wc_payvalida-card-number', '');
				$cart_type = $this->card_to_type($card_number);
        $cart_type = strtoupper($cart_type);
				$franchise = $this->card_to_number($card_number);
				$cuota = $this->input_post('wc_payvalida-card-cuota', '');
				$documento = $this->input_post('wc_payvalida-card-documento', '');
				$card_num = str_replace( array(' ', '-' ), '', $_POST['wc_payvalida-card-number'] );
				$cvv = ( isset( $_POST['wc_payvalida-card-cvc'] ) ) ? $_POST['wc_payvalida-card-cvc'] : '';
				$exp_date = str_replace( array( '/', ' '), '', $_POST['wc_payvalida-card-expiry'] );
				$month =  substr( $exp_date, 0, 2);
				$year = substr( $exp_date, 2, 2);
				$tipoDI =  $this->input_post('wc_payvalida-card-tipodocumento', '');
				$referenceCode = "";
        $ip = $_SERVER['REMOTE_ADDR'];
				// This is where the fun stuff begins
		$payload = array(
			// Payvalida Credentials
			"fixed_hash"           	=> $fished ,
			"x_login"              	=> $this->api_login,
			// Order total
			"x_amount"             	=> $customer_order->order_total,
			// Credit Card Information
			"x_card_num"           	=> str_replace( array(' ', '-' ), '', $_POST['wc_payvalida-card-number'] ),
			"x_card_code"          	=> $cvv,
			"x_exp_date"           	=> str_replace( array( '/', ' '), '', $_POST['wc_payvalida-card-expiry'] ),
			"x_cuota"          	    => $cuota,
			"x_documento"          	=> $documento,
			"x_type"               	=> 'AUTH_CAPTURE',
			"x_invoice_num"        	=> str_replace( "#", "", $customer_order->get_order_number() ),
			"x_test_request"       	=> $environment,
			"x_delim_char"         	=> '|',
			"x_encap_char"         	=> '',
			"x_delim_data"         	=> "TRUE",
			"x_relay_response"     	=> "FALSE",
			"x_method"             	=> "CC",
			// Billing Information
			"x_first_name"         	=> $customer_order->billing_first_name,
			"x_last_name"          	=> $customer_order->billing_last_name,
			"x_address"            	=> $customer_order->billing_address_1,
			"x_city"              	=> $customer_order->billing_city,
			"x_state"              	=> $customer_order->billing_state,
			"x_zip"                	=> $customer_order->billing_postcode,
			"x_country"            	=> $customer_order->billing_country,
			"x_phone"              	=> $customer_order->billing_phone,
			"x_email"              	=> $customer_order->billing_email,
			// Shipping Information
			"x_ship_to_first_name" 	=> $customer_order->shipping_first_name,
			"x_ship_to_last_name"  	=> $customer_order->shipping_last_name,
			"x_ship_to_company"    	=> $customer_order->shipping_company,
			"x_ship_to_address"    	=> $customer_order->shipping_address_1,
			"x_ship_to_city"       	=> $customer_order->shipping_city,
			"x_ship_to_country"    	=> $customer_order->shipping_country,
			"x_ship_to_state"      	=> $customer_order->shipping_state,
			"x_ship_to_zip"        	=> $customer_order->shipping_postcode,
			// information customer
			"x_cust_id"            	=> $customer_order->user_id,
			"x_customer_ip"        	=> $_SERVER['REMOTE_ADDR'],
		);
		// Send this payload to Payvalida for processing
		//Calculate checksum =  SHA512(order + merchant + FIXED_HASH)
		$key = strval($order_id) . $this->api_login . $fished ;
		$checksum =  hash('sha512', $key);
		$service_url = $environment_orders.strval($order_id)."?merchant=".$this->api_login."&checksum=".$checksum;
		$curl = curl_init($service_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type:application/json"]);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);
		$obj = json_decode($response, true);
		if(count($obj['DATA']) > 0){ //Order exists
				$referenceCode =  $obj['DATA'][0]['REFERENCE'];
		}else{ //Create new order
			//Calculate checksum =  SHA512(email + country + order + money + amount + FIXED_HASH)
			 $key = $customer_order->billing_email . strval(343) . strval($order_id) . "COP" . strval($customer_order->order_total) . $fished ;
			 $checksum =  hash('sha512', $key);
			 //Register payment order
       $reference = new DateTime();
			 $myOrder->country = 343;
			 $myOrder->email = $customer_order->billing_email;
			 $myOrder->merchant = $this->api_login;
			 $myOrder->order = strval($order_id);
			 $myOrder->reference = strval($reference->getTimestamp());
			 $myOrder->money = "COP";
       $myOrder->iva = strval($this->api_iva);
			 $myOrder->amount = $customer_order->order_total;
			 $myOrder->description = "Pedido #".strval($order_id);
			 $myOrder->language = "es";
			 $myOrder->recurrent = false;
			 $myOrder->expiration =  strval(date("d/m/Y"));
			 $myOrder->method = "tc";
			 $myOrder->checksum = $checksum;
			 $data = json_encode($myOrder);
			 $curl = curl_init($environment_orders);
			 curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
			 curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			 curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			 curl_setopt($curl, CURLOPT_POST, true);
			 $response = curl_exec($curl);
			 curl_close($curl);
			 $obj = json_decode($response, true);
			$referenceCode =  $obj['DATA'] ['Referencia'];
		}
			 if ($obj['CODE'] !="0000" )
				 throw new Exception( __( $obj->{'DESC'}, 'wc_payvalida' ) );
			 if($obj['CODE'] =="0000" ){
         $reference = $referenceCode;
				 $key = $reference . strval($customer_order->order_total) . $documento . $fished;
				 $checksum =  hash('sha512', $key);
				 //Register Creditcard transaction
				 $myTransaction->merchant = $this->api_login;
				 $myTransaction->reference = $reference;
				 $myTransaction->amount = $customer_order->order_total;
				 $myTransaction->typeDI = $tipoDI;
				 $myTransaction->ip = $ip;
         $myTransaction->name = $name;
         $myTransaction->di = $documento;
				 $myTransaction->franchise = strval($franchise);
				 $myTransaction->cardNumber = $card_num;
				 $myTransaction->expirationMonth = $month;
				 $myTransaction->expirationYear = $year;
				 $myTransaction->cvv = $cvv;
				 $myTransaction->cuotes = $cuota;
				 $myTransaction->checksum = $checksum;
				 $data = json_encode($myTransaction);
				 $curl = curl_init($environment_transactions);
				 curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
				 curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				 curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				 curl_setopt($curl, CURLOPT_POST, true);
				 $response = curl_exec($curl);
				 curl_close($curl);
				$obj = json_decode($response, true);
			 if ( is_wp_error( $response ) )
				 throw new Exception( __( 'Existe un problema para el portal de pago. Lo sentimos por los inconvenientes ocasionados.', 'wc_payvalida' ) );
			 if (  $obj['CODE'] !="0000" )
				 throw new Exception( __( $obj['DESC'], 'wc_payvalida' ) );
			 // get body response while get not error
			 $response_body = wp_remote_retrieve_body( $response );
			 foreach ( preg_split( "/\r?\n/", $response_body ) as $line ) {
				 $resp = explode( "|", $line );
			 }
			 // Payment successful
			 if ( $obj['CODE'] =="0000" ) {
				 $customer_order->add_order_note( __( 'Payvalida pago completado.', 'wc_payvalida' ) );
				 // paid order marked
				 $customer_order->payment_complete();
				 // this is important part for empty cart
				 $woocommerce->cart->empty_cart();
				 // Redirect to thank you page
				 return array(
					 'result'   => 'success',
					 'redirect' => $this->get_return_url( $customer_order ),
				 );
			 } else {
				 //transiction fail
				 wc_add_notice( $obj['DESC'], 'error' );
				 $customer_order->add_order_note( 'Error: '. $obj['DESC'] );
			 }
			 }
    }
    // Validate fields
    public function validate_fields() {
        $card_documento = $this->input_post('wc_payvalida-card-documento', false);
        $card_number = trim($this->input_post('wc_payvalida-card-number', false));
        $expiry_date = $this->input_post('wc_payvalida-card-expiry', false);
        $cvc = $this->input_post('wc_payvalida-card-cvc', false);
				$card_cuota = $this->input_post('wc_payvalida-card-cuota', false);
        // documento de identidad
        if(!$card_documento){
            wc_add_notice(__(
                '<strong>El documento</strong> es requerido',
                'woocommerce'),
            'error');
        }
        // card number
        if(!$card_number){
            wc_add_notice(
                __('<strong>El Número de tarjeta</strong> es requerido',
                   'woocommerce'),
            'error');
        } else {
            $cart_type = $this->card_to_type($card_number, false);
            if(!$cart_type){
              wc_add_notice(
                  __('<strong>Número de tarjeta</strong> no aceptada',
                     'woocommerce' ),
              'error');
            }
        }
        // expiry date
        if(empty($expiry_date)){
            wc_add_notice(
                __('<strong>Fecha de vencimiento de la tarjeta</strong> es requerida',
                   'woocommerce'),
            'error');
        } else {
            list($month, $year) = explode("/", $expiry_date);
            $month = trim($month);
            $year = trim($year);
          if(!empty($month) && !empty($year) && intval($month) <= 12){
                $getExpiry = DateTime::createFromFormat('y-m-d', $year . '-' . $month . '-01');
                $getExpiry->modify('last day of this month');
                $today = new DateTime("now");
                if($today > $getExpiry){
                    wc_add_notice(
                        __('<strong>Tarjeta vencida</strong>',
                           'woocommerce' ),
                    'error');
                }
            }else{
                 wc_add_notice(
                    __('<strong>Fecha de vencimiento de la tarjeta</strong> no es valida',
                       'woocommerce' ),
                'error' );
            }
        }
        // Card Code
        if(!$cvc){
            wc_add_notice(
                __('<strong>Código de tarjeta</strong> es requerido',
                   'woocommerce'),
            'error' );
        }else{
            if(strlen($cvc) < 3){
                wc_add_notice(
                    __('<strong>Código de tarjeta</strong> invalido',
                        'woocommerce' ),
                'error');
            }
        }
        // Cuota
        if(!$card_cuota){
            wc_add_notice(__(
                '<strong>La cuota</strong> es requerida',
                'woocommerce'),
            'error');
        }
        return true;
    }
    /**
     * Get Input
     * @param  string  $name
     * @param  boolean $default
     * @return mixed
     */
    protected function input_post($name, $default = false){
        if(empty($_POST[$name])){
            return $default;
        }
        return $_POST[$name];
    }
}
