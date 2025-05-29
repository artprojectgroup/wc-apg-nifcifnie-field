<?php
//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;

/**
 * Añade los campos en el Pedido.
 */
class APG_Campo_NIF_en_Pedido {
    public  $nombre_nif,
            $placeholder,
            $mensaje_error,
            $priority,
            $mensaje_vies,
            $mensaje_max,    
            $mensaje_eori,
            $listado_paises;

    //Inicializa las acciones de Pedido
    public function __construct() {
        global $apg_nif_settings;
        
        //Listado de países que se pueden validar
        $this->listado_paises   = [
            'AL', //Albania
            'AT', //Austria
            'AR', //Argentina
            'AX', //Islas de Åland
            'BE', //Bélgica 
            'BG', //Bulgaria 
            'BY', //Bielorusia 
            'CH', //Suiza
            'CL', //Chile
            'CY', //Chipre 
            'CZ', //República Checa
            'DE', //Alemania 
            'DK', //Dinamarca 
            'EE', //Estonia 
            'ES', //España 
            'EU', //Unión Europea 
            'FI', //Finlandia
            'FO', //Islas Feroe
            'FR', //Francia 
            'GB', //Gran Bretaña 
            'GR', //Grecia
            'HR', //Croacia 
            'HU', //Hungría 
            'IE', //Irlanda
            'IS', //Islandia 
            'IT', //Italia 
            'LI', //Liechtenstein
            'LT', //Lituania 
            'LU', //Luxemburgo 
            'LV', //Letonia
            'MC', //Mónaco
            'MD', //Moldavia
            'ME', //Montenegro
            'MK', //Macedonia del Norte
            'MT', //Malta 
            'NL', //Países Bajos 
            'NO', //Noruega 
            'PL', //Polonia 
            'PT', //Portugal 
            'RO', //Rumanía 
            'RS', //Serbia 
            'SE', //Suecia             
            'SI', //Eslovenia 
            'SK', //República Eslovaca
            'SM', //San Marino
            'UA', //Ucrania
        ];
        
        add_filter( 'woocommerce_default_address_fields', [ $this, 'apg_nif_campos_de_direccion' ] );
        add_filter( 'woocommerce_billing_fields', [ $this, 'apg_nif_formulario_de_facturacion' ] );
        add_filter( 'woocommerce_shipping_fields', [ $this, 'apg_nif_formulario_de_envio' ] );
        add_action( 'after_setup_theme', [ $this, 'apg_nif_traducciones' ] );
        //Bloques
        add_action( 'woocommerce_init', [ $this, 'apg_nif_formulario_bloques' ] );
        add_action( 'woocommerce_set_additional_field_value', [ $this, 'apg_nif_retrocompatibilidad_formulario_bloques' ], 10, 4 );
        add_filter( 'woocommerce_get_default_value_for_apg/nif', [ $this, 'apg_nif_retrocompatibilidad_campo_formulario_bloques' ], 10, 3 );
        add_action( 'woocommerce_blocks_loaded', [ $this, 'apg_nif_recarga_checkout' ] );

        //Validación
        $valida_en_checkout = (
            ( isset( $apg_nif_settings[ 'validacion' ] ) && $apg_nif_settings[ 'validacion' ] === '1' ) ||  //Valida el campo NIF/CIF/NIE
            ( isset( $apg_nif_settings[ 'validacion_vies' ] ) && $apg_nif_settings[ 'validacion_vies' ] === '1' ) || //Valida el número VIES
            ( isset( $apg_nif_settings[ 'validacion_eori' ] ) && $apg_nif_settings[ 'validacion_eori' ] === '1' ) //Valida el número EORI
        );

        if ( $valida_en_checkout ) {
            add_action( 'woocommerce_checkout_process', [ $this, 'apg_nif_validacion_de_campo' ] );
            add_action( 'woocommerce_blocks_validate_location_address_fields', [ $this, 'apg_nif_validacion_de_campo_bloques' ], 10, 3 ); //Formulario de bloques
        }

        //Carga el JavaScript
        $necesita_ajax = (
            ( isset( $apg_nif_settings[ 'validacion' ] ) && $apg_nif_settings[ 'validacion' ] === '1' ) || //Valida el campo NIF/CIF/NIE
            ( isset( $apg_nif_settings[ 'validacion_vies' ] ) && $apg_nif_settings[ 'validacion_vies' ] === '1' ) || //Valida el número VIES
            ( isset( $apg_nif_settings[ 'validacion_eori' ] ) && $apg_nif_settings[ 'validacion_eori' ] === '1' ) //Valida el número EORI
        );

        if ( $necesita_ajax ) {
            if ( function_exists( 'has_block' ) && has_block( 'woocommerce/checkout', wc_get_page_id( 'checkout' ) ) ) {
                add_action( 'enqueue_block_assets', [ $this, 'apg_nif_carga_ajax' ] );
            } else {
                add_action( 'wp_enqueue_scripts', [ $this, 'apg_nif_carga_ajax' ] );
            }
        }
        
        //Añade validación al vuelo
        if ( isset( $apg_nif_settings[ 'validacion' ] ) && $apg_nif_settings[ 'validacion' ] === '1' ) {            
            add_action( 'wp_ajax_nopriv_apg_nif_valida_VAT', [ $this, 'apg_nif_valida_VAT' ] );
            add_action( 'wp_ajax_apg_nif_valida_VAT', [ $this, 'apg_nif_valida_VAT' ] );
        }
        
        //Añade el número VIES
        if ( isset( $apg_nif_settings[ 'validacion_vies' ] ) && $apg_nif_settings[ 'validacion_vies' ] === "1" ) {
            add_action( 'wp_ajax_nopriv_apg_nif_valida_VIES', [ $this, 'apg_nif_valida_VIES' ] );
            add_action( 'wp_ajax_apg_nif_valida_VIES', [ $this, 'apg_nif_valida_VIES' ] );
            add_action( 'woocommerce_checkout_update_order_review', [ $this, 'apg_nif_quita_iva' ] );
        }
        //Añade el número EORI
        if ( isset( $apg_nif_settings[ 'validacion_eori' ] ) && $apg_nif_settings[ 'validacion_eori' ] === "1" ) {
            add_action( 'wp_ajax_nopriv_apg_nif_valida_EORI', [ $this, 'apg_nif_valida_EORI' ] );
            add_action( 'wp_ajax_apg_nif_valida_EORI', [ $this, 'apg_nif_valida_EORI' ] );
        }
    }

    //Añade las traducciones
    public function apg_nif_traducciones() {
        global $apg_nif_settings;

        $this->nombre_nif       = isset( $apg_nif_settings[ 'etiqueta' ] ) && $apg_nif_settings[ 'etiqueta' ] ? sanitize_text_field( $apg_nif_settings[ 'etiqueta' ] ) : esc_attr__( 'NIF/CIF/NIE', 'wc-apg-nifcifnie-field' ); //Nombre original del campo
        $this->placeholder      = isset( $apg_nif_settings[ 'placeholder' ] ) && $apg_nif_settings[ 'placeholder' ] ? sanitize_text_field( $apg_nif_settings[ 'placeholder' ] ) : esc_attr_x( 'NIF/CIF/NIE number', 'placeholder', 'wc-apg-nifcifnie-field' ); //Nombre original del placeholder
        $this->mensaje_error    = isset( $apg_nif_settings[ 'error' ] ) && $apg_nif_settings[ 'error' ] ? sanitize_text_field( $apg_nif_settings[ 'error' ] ) : esc_attr__( 'Please enter a valid NIF/CIF/NIE.', 'wc-apg-nifcifnie-field' ); //Mensaje de error
        $this->priority         = ( isset( $apg_nif_settings[ 'prioridad' ] ) ? esc_attr( $apg_nif_settings[ 'prioridad' ] ) : 31 ); //Prioridad del campo
                
        //Número VIES
        if ( isset( $apg_nif_settings[ 'validacion_vies' ] ) && $apg_nif_settings[ 'validacion_vies' ] == "1" ) {
            $this->nombre_nif   = isset( $apg_nif_settings[ 'etiqueta_vies' ] ) && $apg_nif_settings[ 'etiqueta_vies' ] ? sanitize_text_field( $apg_nif_settings[ 'etiqueta_vies' ] ) : esc_attr__( 'NIF/CIF/NIE/VAT number', 'wc-apg-nifcifnie-field' ); //Nombre modificado del campo
            $this->placeholder  = isset( $apg_nif_settings[ 'placeholder_vies' ] ) && $apg_nif_settings[ 'placeholder_vies' ] ? sanitize_text_field( $apg_nif_settings[ 'placeholder_vies' ] ) : esc_attr_x( 'NIF/CIF/NIE/VAT number', 'placeholder', 'wc-apg-nifcifnie-field' ); //Nombre modificado del placeholder
            $this->mensaje_vies = isset( $apg_nif_settings[ 'error_vies' ] ) && $apg_nif_settings[ 'error_vies' ] ? sanitize_text_field( $apg_nif_settings[ 'error_vies' ] ) : esc_attr__( 'Please enter a valid VIES VAT number.', 'wc-apg-nifcifnie-field' ); //Mensaje de error
            $this->mensaje_max  = isset( $apg_nif_settings[ 'error_vies_max' ] ) && $apg_nif_settings[ 'error_vies_max' ] ? sanitize_text_field( $apg_nif_settings[ 'error_vies_max' ] ) : esc_attr__( 'Error: maximum number of concurrent requests exceeded.', 'wc-apg-nifcifnie-field' ); //Mensaje de error
        }
        //Número EORI
        if ( isset( $apg_nif_settings[ 'validacion_eori' ] ) && $apg_nif_settings[ 'validacion_eori' ] == "1" ) {
            $this->nombre_nif   = isset( $apg_nif_settings[ 'etiqueta_eori' ] ) && $apg_nif_settings[ 'etiqueta_eori' ] ? sanitize_text_field( $apg_nif_settings[ 'etiqueta_eori' ] ) : esc_attr__( 'NIF/CIF/NIE/EORI number', 'wc-apg-nifcifnie-field' ); //Nombre modificado del campo
            $this->placeholder  = isset( $apg_nif_settings[ 'placeholder_eori' ] ) && $apg_nif_settings[ 'placeholder_eori' ] ? sanitize_text_field( $apg_nif_settings[ 'placeholder_eori' ] ) : esc_attr_x( 'NIF/CIF/NIE/EORI number', 'placeholder', 'wc-apg-nifcifnie-field' ); //Nombre modificado del placeholder
            $this->mensaje_eori = isset( $apg_nif_settings[ 'error_eori' ] ) && $apg_nif_settings[ 'error_eori' ] ? sanitize_text_field( $apg_nif_settings[ 'error_eori' ] ) : esc_attr__( 'Please enter a valid EORI number.', 'wc-apg-nifcifnie-field' ); //Mensaje de error
        }
    }

    //Arregla la dirección predeterminada
    public function apg_nif_campos_de_direccion( $campos ) {
        global $apg_nif_settings;

        //Sólo es operativo en los checkout clásicos
        if ( WC_Blocks_Utils::has_block_in_page( wc_get_page_id( 'checkout' ), 'woocommerce/checkout' ) ) {
            return $campos;
        }
        
        $campos[ 'nif' ]    = [
            'label'         => $this->nombre_nif,
            'placeholder'   => $this->placeholder,
            'priority'      => $this->priority,
            'required'      => isset( $apg_nif_settings[ 'requerido' ] ) && $apg_nif_settings[ 'requerido' ] === '1',
        ];
        
        if ( apply_filters( 'apg_nif_add_fields', true ) ) { //Si no quieren añadirse: add_filter( 'apg_nif_add_fields', '__return_false' );
            //Añade el correo electónico y el teléfono
            $campos[ 'email' ]  = [
                'label'         => esc_attr__( 'Email address', 'wc-apg-nifcifnie-field' ),
                'required'      => true,
                'type'          => 'email',
                'validate'      => [
                    'email'
                ],
                'autocomplete'  => 'email username',
                'priority'      => 110,
            ];
            $campos[ 'phone' ]  = [
                'label'         => esc_attr__( 'Phone', 'wc-apg-nifcifnie-field' ),
                'required'      => true,
                'type'          => 'tel',
                'validate'      => [
                    'phone'
                ],
                'autocomplete'  => 'tel',
                'priority'      => 100,
            ];
        }

        //Fuerza la actualización del checkout con el código postal y la provincia/estado
        $campos[ 'postcode' ][ 'class' ][]  = 'update_totals_on_change';
        $campos[ 'state' ][ 'class' ][]     = 'update_totals_on_change'; 

        return $campos;
    }

    //Arregla el formulario de facturación
    public function apg_nif_formulario_de_facturacion( $campos ) {
        global $apg_nif_settings;

        $is_checkout = is_checkout() || ( defined( 'WOOCOMMERCE_CHECKOUT' ) && WOOCOMMERCE_CHECKOUT ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX );
        $campos[ 'billing_nif' ][ 'required' ]  = ( isset( $apg_nif_settings[ 'requerido' ] ) && $apg_nif_settings[ 'requerido' ] === '1' && $is_checkout );
        
        return $campos;
    }
    
    //Formulario de bloques
    public function apg_nif_formulario_bloques() {
        global $apg_nif_settings;

        $etiqueta   = $this->nombre_nif;
        if ( function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
            woocommerce_register_additional_checkout_field( [
                'id'            => 'apg/nif',
                'label'         => $etiqueta,
                // translators: %s is the field label (e.g., NIF/CIF/NIE)
                'optionalLabel' => sprintf( esc_attr__( '%s (optional)', 'wc-apg-nifcifnie-field' ), $etiqueta ),
                'location'      => 'address',
                'required'      => isset( $apg_nif_settings[ 'requerido' ] ) && $apg_nif_settings[ 'requerido' ] === '1',
                'type'          => 'text',
                'attributes' => [
                    'autocomplete'      => 'nif',
                    'data-attribute'    => 'nif',
                    'title'             => $this->placeholder,
                ],
            ] );              
        }

        if ( function_exists( 'woocommerce_store_api_register_additional_field' ) ) {
            woocommerce_store_api_register_additional_field(
                'apg/nif', [
                    'getter' => function ( $order, $group ) {
                        $meta_key   = ( 'billing' === $group ) ? '_billing_nif' : '_shipping_nif';
                        return $order->get_meta( $meta_key );
                    },
                    'setter' => function ( $order, $value, $group ) {
                        $meta_key   = ( 'billing' === $group ) ? '_billing_nif' : '_shipping_nif';
                        $order->update_meta_data( $meta_key, sanitize_text_field( $value ) );
                    },
                    'schema' => [
                        'description'   => $this->nombre_nif,
                        'type'          => 'string',
                        'context'       => [ 'view', 'edit' ],
                    ],
                ]
            );
        }
        
        //Limpia el campo NIF/CIF/NIE
        add_action( 'woocommerce_sanitize_additional_field', function( $field_value, $field_key ) {
            if ( 'apg/nif' === $field_key ) {
                $field_value    = sanitize_text_field( strtoupper( trim( $field_value ) ) );
            }

            return $field_value;
        }, 10 , 2 );
        
        //Añade la prioridad al campo
        add_filter( 'woocommerce_get_country_locale', function( $locale ) {
            $paises = WC()->countries->get_countries();
            foreach ( $paises as $iso => $pais ) {
                $locale[ $iso ][ 'apg/nif' ][ 'priority' ]   = $this->priority;
            }
            
            return $locale;
        } );
	}
    
    //Actualiza el checkout
    public function apg_nif_recarga_checkout() {
        if ( function_exists( 'woocommerce_store_api_register_update_callback' ) ) {
            woocommerce_store_api_register_update_callback( [
                    'namespace' => 'apg_nif_valida_vies',
                    'callback'  => function() {
                        $this->apg_nif_quita_iva( true );
                    },
            ] );
       }
    }
    
    //Añade retrocompatibilidad al formulario de bloques 
    public function apg_nif_retrocompatibilidad_formulario_bloques( $key, $value, $group, $wc_object ) {
		if ( 'apg/nif' !== $key ) {
			return;
		}

		$clave    = ( 'billing' === $group ) ? '_billing_nif' : '_shipping_nif';

		$wc_object->update_meta_data( $clave, $value, true );
	}
    public function apg_nif_retrocompatibilidad_campo_formulario_bloques( $value, $group, $wc_object ) {
		$clave    = ( 'billing' === $group ) ? '_billing_nif' : '_shipping_nif';

		return $wc_object->get_meta( $clave );
	}
    
    //Arregla el formulario de envío
    public function apg_nif_formulario_de_envio( $campos ) {
        global $apg_nif_settings;

        $facturacion    = WC()->countries->get_address_fields( WC()->countries->get_base_country(), 'billing_' );

        $campos[ 'shipping_nif' ][ 'required' ]     = isset( $apg_nif_settings[ 'requerido_envio' ] ) && $apg_nif_settings[ 'requerido_envio' ] === '1';
        if ( apply_filters( 'apg_nif_add_fields', true ) ) { //Si no quieren añadirse: add_filter( 'apg_nif_add_fields', '__return_false' );
            $campos[ 'shipping_email' ][ 'priority' ]   = $facturacion[ 'billing_email' ][ 'priority' ];
            $campos[ 'shipping_phone' ][ 'priority' ]   = $facturacion[ 'billing_phone' ][ 'priority' ];
        }
        
        return $campos;
    }

     //Valida el campo VAT number
     public function apg_nif_validacion_internacional( $vat_number, $vat_country, $pais ) {
         //Carga el validador
        require_once plugin_dir_path( __FILE__ ) . 'validador.php';

        //Limpia y normaliza
        $vat_number     = preg_replace( '/[^A-Z0-9]/', '', strtoupper( $vat_number ) );
        $vat_country    = strtoupper( $vat_country );
        $pais           = strtoupper( $pais );

        //Detecta prefijo de país en el número VAT (dos letras iniciales)
        $prefijo_detectado  = '';
        if ( preg_match( '/^[A-Z]{2}/', $vat_number, $match ) ) {
            $prefijo_detectado  = $match[ 0 ];
        }

        //Prioriza el prefijo detectado, luego el país explícito, luego el de facturación
        $valida_pais    = $prefijo_detectado ?: ( preg_match( '/^[A-Z]{2}$/', $pais ) ? $pais : $vat_country );

        //Elimina el prefijo del número para validar
        if ( $prefijo_detectado ) {
            $vat_number = substr( $vat_number, 2 );
        }

        //Valida países específicos
        switch ( $valida_pais ) {
                /*
            case 'AR': //Argentina
                return apg_nif_valida_ar( $vat_number );
            case 'AT': //Austria
                return apg_nif_valida_at( $vat_number );
            case 'BE': //Bélgica
                return apg_nif_valida_be( $vat_number );
            case 'BG': //Bulgaria
                return apg_nif_valida_bg( $vat_number );
            case 'CL': //Chile
                return apg_nif_valida_cl( $vat_number );
            case 'CZ': //República Checa
                return apg_nif_valida_cz( $vat_number );
            case 'DE': //Alemania
                return apg_nif_valida_de( $vat_number );
            case 'DK': //Dinamarca
                return apg_nif_valida_dk( $vat_number );
            case 'EE': //Estonia
                return apg_nif_valida_ee( $vat_number );
            case 'EL': //Grecia (código alternativo a GR)
            case 'GR': //Grecia
                return apg_nif_valida_gr( $vat_number );
            case 'ES': //España
                return apg_nif_valida_es( $vat_number );
            case 'FI': //Finlandia
                return apg_nif_valida_fi( $vat_number );
            case 'FR': //Francia
                return apg_nif_valida_fr( $vat_number );
            case 'HR': //Croacia
                return apg_nif_valida_hr( $vat_number );
            case 'HU': //Hungría
                return apg_nif_valida_hu( $vat_number );
            case 'IE': //Irlanda
                return apg_nif_valida_ie( $vat_number );
            case 'IT': //Italia
                return apg_nif_valida_it( $vat_number );
            case 'LT': //Lituania
                return apg_nif_valida_lt( $vat_number );
            case 'LU': //Luxemburgo
                return apg_nif_valida_lu( $vat_number );
            case 'LV': //Letonia
                return apg_nif_valida_lv( $vat_number );
            case 'MT': //Malta
                return apg_nif_valida_mt( $vat_number );
            case 'NL': //Países Bajos
                return apg_nif_valida_nl( $vat_number );
            case 'NO': //Noruega
                return apg_nif_valida_no( $vat_number );
            case 'PL': //Polonia
                return apg_nif_valida_pl( $vat_number );
            case 'PT': //Portugal
                return apg_nif_valida_pt( $vat_number );
            case 'RO': //Rumanía
                return apg_nif_valida_ro( $vat_number );
            case 'SE': //Suecia
                return apg_nif_valida_se( $vat_number );
            case 'SI': //Eslovenia
                return apg_nif_valida_si( $vat_number );
            case 'SK': //Eslovaquia
                return apg_nif_valida_sk( $vat_number );
                */
            case 'AR': //Argentina
                return apg_nif_valida_ar( $vat_number );
            case 'CL': //Chile
                return apg_nif_valida_cl( $vat_number );
            case 'ES': //España
                return apg_nif_valida_es( $vat_number );
            default:
                return apg_nif_valida_regex( $valida_pais, $vat_number );
        }
    }

    //Valida el campo NIF/CIF/NIE
    public function apg_nif_validacion_de_campo() {
        global $apg_nif_settings;

        //Procesa los camops
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'get-customer-details'
        $billing_nif        = isset( $_POST[ 'billing_nif' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'billing_nif' ] ) ) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'get-customer-details'
        $billing_country    = isset( $_POST[ 'billing_country' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'billing_country' ] ) ) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'get-customer-details'
        $shipping_nif       = isset( $_POST[ 'shipping_nif' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'shipping_nif' ] ) ) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'get-customer-details'
        $shipping_country   = isset( $_POST[ 'shipping_country' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'shipping_country' ] ) ) : '';

        //Confirma si es requerido
        $es_requerido       = isset( $apg_nif_settings[ 'requerido' ] ) && $apg_nif_settings[ 'requerido' ] === '1';
        $es_requerido_envio = isset( $apg_nif_settings[ 'requerido_envio' ] ) && $apg_nif_settings[ 'requerido_envio' ] === '1';
        
        //Validación para el formulario de facturación
        if ( ( $billing_nif || $es_requerido ) && isset( $apg_nif_settings[ 'validacion' ] ) && $apg_nif_settings[ 'validacion' ] === '1' ) {
            $validacion     = $this->apg_nif_valida_exencion( $billing_nif, $billing_country );

            //Mensaje de error
            if ( ! $validacion[ 'usar_eori' ] && ! $validacion[ 'valido_vies' ] && ! $validacion[ 'vat_valido' ] ) {
                wc_add_notice( $this->mensaje_error, 'error' );
            }

            //Muestra el mensaje de error personalizado
            if ( apply_filters( 'apg_nif_display_error_message', false, $billing_nif, $billing_country ) ) {
                $mensaje = apply_filters( 'apg_nif_error_message', $this->mensaje_error, $billing_nif, $billing_country );
                wc_add_notice( $mensaje, 'error' );
            }

            //Validación EORI
            if ( isset( $apg_nif_settings[ 'validacion_eori' ] ) && $apg_nif_settings[ 'validacion_eori' ] === '1' ) {
                if ( $validacion[ 'usar_eori' ] && ! $validacion[ 'valido_eori' ] ) {
                    wc_add_notice( $this->mensaje_eori, 'error' );
                }
            }
        }

        //Validación para el formulario de envío
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'get-customer-details'
        if ( ( $shipping_nif || $es_requerido_envio ) && isset( $_POST[ 'ship_to_different_address' ] ) ) {
            $validacion_envio   = $this->apg_nif_valida_exencion( $shipping_nif, $shipping_country );

            //Mensaje de error
            if ( ! $validacion_envio[ 'usar_eori' ] && ! $validacion_envio[ 'valido_vies' ] && ! $validacion_envio[ 'vat_valido' ] ) {
                wc_add_notice( $this->mensaje_error . ' - ' . esc_attr__( 'Shipping details', 'wc-apg-nifcifnie-field' ), 'error' );
            }

            //Validación EORI
            if ( isset( $apg_nif_settings[ 'validacion_eori' ] ) && $apg_nif_settings[ 'validacion_eori' ] === '1' ) {
                if ( $validacion_envio[ 'usar_eori' ] && ! $validacion_envio[ 'valido_eori' ] ) {
                    wc_add_notice( $this->mensaje_eori, 'error' );
                }
            }
        }
    }

    //Valida el campo NIF/CIF/NIE - Bloques
    public function apg_nif_validacion_de_campo_bloques( WP_Error $errors, $fields, $group ) {
        global $apg_nif_settings;

        if ( ! defined( 'REST_REQUEST' ) || REST_REQUEST !== true ) {
            return $errors;
        }

        if ( ! WC_Blocks_Utils::has_block_in_page( wc_get_page_id( 'checkout' ), 'woocommerce/checkout' ) ) {
            return $errors;
        }
        
        //Procesa los camops
        $nif            = isset( $fields[ 'apg/nif' ] ) ? sanitize_text_field( $fields[ 'apg/nif' ] ) : '';
        $pais           = isset( $fields[ 'country' ] ) ? sanitize_text_field( $fields[ 'country' ] ) : '';

        //Confirma si es requerido
        $es_requerido   = isset( $apg_nif_settings[ 'requerido' ] ) && $apg_nif_settings[ 'requerido' ] === '1';

        //Solo valida si el campo está relleno o es obligatorio
        if ( ( $nif || $es_requerido ) && isset( $apg_nif_settings[ 'validacion' ] ) && $apg_nif_settings[ 'validacion' ] === '1' ) {
            $validacion = $this->apg_nif_valida_exencion( $nif, $pais );

            //Comprueba si es un número VAT válido
            if ( ! $validacion[ 'usar_eori' ] && ! $validacion[ 'valido_vies' ] && ! $validacion[ 'vat_valido' ] ) {
                $errors->add( 'invalid_vat', $this->mensaje_error );
            }

            //Muestra el mensaje de error personalizado
            if ( apply_filters( 'apg_nif_display_error_message', false, $nif, $pais ) ) {
                $mensaje = apply_filters( 'apg_nif_error_message', $this->mensaje_error, $nif, $pais );
                $errors->add( 'invalid_vat', $mensaje );
            }

            //Validación EORI
            if ( isset( $apg_nif_settings[ 'validacion_eori' ] ) && $apg_nif_settings[ 'validacion_eori' ] === '1' ) {
                if ( $validacion[ 'usar_eori' ] && ! $validacion[ 'valido_eori' ] ) {
                    $errors->add( 'invalid_eori', $this->mensaje_eori );
                }
            }
        }
        
        return $errors;
    }

    //Carga el JavaScript necesario
    public function apg_nif_carga_ajax() {
        global $apg_nif_settings;

        //Funcional únicamente en el checkout
        if ( ! is_checkout() ) {
            return;
        }
    
        //Detección segura del tipo de checkout
        $is_blocks_checkout = function_exists( 'has_block' ) && has_block( 'woocommerce/checkout', wc_get_page_id( 'checkout' ) );

        //Script y manejador unificado
        $script_handle      = 'apg_nif_validacion';
        $script_file        = $is_blocks_checkout ? 'valida-bloques-nif.js' : 'valida-nif.js';
        
        //Sólo carga si está correctamente definido
        if ( defined( 'DIRECCION_apg_nif' ) && defined( 'VERSION_apg_nif' ) ) {
            //Evita cargar el script más de una vez
            if ( ! wp_script_is( $script_handle, 'enqueued' ) ) {
                wp_enqueue_script( $script_handle, plugin_dir_url( DIRECCION_apg_nif ) . 'assets/js/' . $script_file, [ 'jquery' ], VERSION_apg_nif, true );
            }
            
            //Evalúa la prioridad antes de pasarla al JavaScript
            $tipo_validacion = 'vat';
            if ( isset( $apg_nif_settings[ 'validacion_eori' ] ) && $apg_nif_settings[ 'validacion_eori' ] === '1' ) {
                $tipo_validacion = 'eori';
            } elseif ( isset( $apg_nif_settings[ 'validacion_vies' ] ) && $apg_nif_settings[ 'validacion_vies' ] === '1' ) {
                $tipo_validacion = 'vies';
            }
            
            //Localiza variables compartidas con JavaScript
            wp_localize_script( $script_handle, 'apg_nif_ajax', [
                'url'           => admin_url( 'admin-ajax.php' ),
                'vies_error'    => $this->mensaje_vies,
                'max_error'     => $this->mensaje_max,
                'vat_error'     => $this->mensaje_error,
                'eori_error'    => $this->mensaje_eori,
                'validacion'    => $tipo_validacion,
                'nonce'         => wp_create_nonce( 'apg_nif_nonce' ),
            ] );
        }
    }
    
    //Valida la excepción de impuestos
    public function apg_nif_valida_exencion( string $nif, string $pais_cliente ): array {
        global $apg_nif_settings;

        $pais_base      = WC()->countries->get_base_country();
        $prefijo_nif    = strtoupper( substr( $nif, 0, 2 ) );
        $pais_cliente   = strtoupper( $pais_cliente );

        $eori_activo    = isset( $apg_nif_settings[ 'validacion_eori' ] ) && $apg_nif_settings[ 'validacion_eori' ] === '1';
        $vies_activo    = isset( $apg_nif_settings[ 'validacion_vies' ] ) && $apg_nif_settings[ 'validacion_vies' ] === '1';
        $eori_paises    = $apg_nif_settings[ 'eori_paises' ] ?? [];

        //Si coincide país del cliente y prefijo, se valida EORI
        $usar_eori      = $eori_activo && in_array( $pais_cliente, $eori_paises, true ) && in_array( $prefijo_nif, $eori_paises, true );

        $valido_eori    = false;
        $valido_vies    = false;
        $vat_valido     = ( in_array( $pais_cliente, $this->listado_paises, true ) ) ? $this->apg_nif_validacion_internacional( $nif, $pais_cliente, $prefijo_nif ) : true;

        if ( $usar_eori ) {
            $valido_eori    = $this->apg_nif_comprobacion_eori( $nif, $pais_cliente );
        } elseif ( $vies_activo ) {
            $valido_vies    = $this->apg_nif_comprobacion_vies( $nif, $pais_cliente );
        }

        $es_exento  = ( $valido_vies && $pais_cliente !== $pais_base && $prefijo_nif !== $pais_base );

        return [
            'es_exento'     => $es_exento,
            'valido_vies'   => $valido_vies,
            'valido_eori'   => $valido_eori,
            'usar_eori'     => $usar_eori,
            'vat_valido'    => $vat_valido,
        ];
    }

    //Quita impuestos si el NIF/VAT es válido en VIES
    public function apg_nif_quita_iva( $actualiza = false) {
        if ( $actualiza || is_checkout() || defined( 'WOOCOMMERCE_CHECKOUT' ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            if ( WC()->session ) {
                $valor  = WC()->session->get( 'apg_nif' );
                $exento = $valor === true || $valor === '1' || $valor === 1;
                WC()->customer->set_is_vat_exempt( $exento );
            }
        }
    }
    
    //Recoge los datos para el campo NIF/CIF/NIE, VIES y EORI
    private function apg_nif_recoge_datos_ajax(): array {
        //No funciona en el panel de administración (excepto AJAX)
        if ( is_admin() && ! wp_doing_ajax() ) {
            wp_send_json_error( __( 'Invalid context.', 'wc-apg-nifcifnie-field' ) );
        }

        //Verifica el nonce
        check_ajax_referer( 'apg_nif_nonce', 'nonce' );

        //Procesa los campos
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'get-customer-details'
        $pais   = isset( $_POST[ 'billing_country' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'billing_country' ] ) ) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'get-customer-details'
        $nif    = isset( $_POST[ 'billing_nif' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'billing_nif' ] ) ) : '';

        return [ $nif, $pais ];
    }
    
    //Valida el campo NIF/CIF/NIE
    public function apg_nif_valida_VAT() {
        list( $nif, $pais ) = $this->apg_nif_recoge_datos_ajax();
        $prefijo_nif        = strtoupper( substr( $nif, 0, 2 ) );
        $valido             = $this->apg_nif_validacion_internacional( $nif, $pais, $prefijo_nif );

        wp_send_json_success( [ 'vat_valido' => $valido ] );
    }
    
    //Valida el campo VIES
    public function apg_nif_valida_VIES() {
        list( $nif, $pais ) = $this->apg_nif_recoge_datos_ajax();
        $resultado          = $this->apg_nif_valida_exencion( $nif, $pais );
        WC()->session->set( 'apg_nif', $resultado[ 'es_exento' ] );
        
        wp_send_json_success( $resultado );
    }
    
    //Valida el campo EORI
    public function apg_nif_valida_EORI() {
        list( $nif, $pais ) = $this->apg_nif_recoge_datos_ajax();
        $resultado          = $this->apg_nif_valida_exencion( $nif, $pais );
        WC()->session->set( 'apg_eori', $resultado[ 'valido_eori' ] );
        
        wp_send_json_success( $resultado );
    }
    
    //Realiza comprobaciones previas al VIES
    public function apg_nif_comprobacion_vies( string $nif, string $pais_tienda ) {
        global $apg_nif_settings;

        if ( empty( $nif ) || empty( $pais_tienda ) ) {
            return false;
        }

        $pais_vies      = strtoupper( substr( $nif, 0, 2 ) );
        //Listado de países válidos
        $paises_validos = [
            "AT",
            "BE",
            "BG",
            "CY",
            "CZ",
            "DE",
            "DK",
            "EE",
            "EL",
            "ES",
            "EU",
            "FI",
            "FR",
            "GR",
            "HR",
            "HU",
            "IE",
            "IT",
            "LT",
            "LU",
            "LV",
            "MT",
            "NL",
            "PL",
            "PT",
            "RO",
            "SE",
            "SI",
            "SK",
        ];

        // Si el país del NIF no es VIES-compatible, no validar
        if ( ! in_array( $pais_vies, $paises_validos, true ) ) {
            return false;
        }

        // Si el país requiere EORI, no validar VIES
        if ( isset( $apg_nif_settings[ 'validacion_eori' ], $apg_nif_settings[ 'eori_paises' ] ) && $apg_nif_settings[ 'validacion_eori' ] === '1' && in_array( $pais_vies, $apg_nif_settings[ 'eori_paises' ], true ) && in_array( $pais_tienda, $apg_nif_settings[ 'eori_paises' ], true ) ) {
            return false;
        }

        return $this->apg_nif_es_valido_vies( $nif, $pais_vies );
    }

    //Comprueba la validez del VIES
    public function apg_nif_es_valido_vies( string $nif_completo, string $pais_de_facturacion ) {
        //Procesa los camops
        $pais       = strtoupper( substr( $nif_completo, 0, 2 ) );
        $nif        = preg_replace( '/^[A-Z]{2}/', '', strtoupper( $nif_completo ) );

        // Hack para Grecia
        $iso_vies   = [ 'EL' => 'GR' ];

        if ( isset( $iso_vies[ $pais ] ) ) {
            $pais   = $iso_vies[ $pais ];
        }

        if ( array_search( $pais, $iso_vies, true ) ) {
            $pais   = array_search( $pais, $iso_vies, true );
        }
        
        //Gestión de caché
        $cache_key  = 'apg_vies_' . md5( $pais . '_' . $nif );
        $cached     = get_transient( $cache_key );
        if ( $cached !== false ) {
            return $cached;
        }

        //Comprueba el VIES
        try {
            $soap       = new SoapClient( 'https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl' );
            $respuesta  = $soap->checkVat( [
                'countryCode'   => $pais,
                'vatNumber'     => $nif,
            ] );
            $resultado  = isset( $respuesta->valid ) && $respuesta->valid === true;
            //Guarda en caché
            set_transient( $cache_key, $resultado, 30 * DAY_IN_SECONDS );
            return $resultado;
        } catch ( SoapFault $e ) {
            //Comprueba el VIES por otra vía
            $respuesta  = wp_remote_get( "https://ec.europa.eu/taxation_customs/vies/rest-api/ms/$pais/vat/$nif" );
            if ( is_wp_error( $respuesta ) ) {
                return false; // error de conexión
            }
            
            $data       = json_decode( wp_remote_retrieve_body( $respuesta ) );
            
            // Devuelve 44 si hay error de concurrencia o servicio no disponible
            if ( isset( $data->userError ) && in_array( $data->userError, [ 'MS_MAX_CONCURRENT_REQ', 'MS_UNAVAILABLE' ], true ) ) {
                return 44;
            }
            $resultado  = isset( $data->isValid ) && $data->isValid === true;
            //Guarda en caché
            set_transient( $cache_key, $resultado, 30 * DAY_IN_SECONDS );            
            return $resultado;
        }
        
        return false;
    }
    
    //Realiza comprobaciones previas al EORI    
    public function apg_nif_comprobacion_eori( string $nif, string $pais ): bool {
        global $apg_nif_settings;

        if ( empty( $nif ) || empty( $pais ) ) {
            return false;
        }

        if ( ! isset( $apg_nif_settings[ 'validacion_eori' ] ) || $apg_nif_settings[ 'validacion_eori' ] !== '1' ) {
            return true;
        }

        if ( ! isset( $apg_nif_settings[ 'eori_paises' ] ) || ! in_array( $pais, $apg_nif_settings[ 'eori_paises' ], true ) ) {
            return true;
        }

        return $this->apg_nif_es_valido_eori( $nif, $pais );
    }    
    
    //Comprueba la validez del EORI
    public function apg_nif_es_valido_eori( string $nif, string $pais ): bool {
        //Gestión de caché
        $cache_key  = 'apg_eori_' . md5( $pais . '_' . $nif );
        $cached     = get_transient( $cache_key );
        if ( $cached !== false ) {
            return $cached;
        }

        //Listado de países para validar en https://vatapp.net
        $paises_vatapp  = [ 'NO', 'CH', 'TH' ];

        //Validación HMRC (UK sin XI)
        if ( $pais === 'GB' && strpos( $nif, 'XI' ) === false ) {
            $response   = wp_remote_post( 'https://api.service.hmrc.gov.uk/customs/eori/lookup/check-multiple-eori', [
                'headers'   => [ 'Content-Type' => 'application/json' ],
                'body'      => json_encode( [ 'eoris' => [ $nif ] ] ),
            ] );
            $eori       = json_decode( wp_remote_retrieve_body( $response ) );
            $resultado  = isset( $eori[ 0 ]->valid ) && $eori[ 0 ]->valid === true;
            //Guarda en caché
            set_transient( $cache_key, $resultado, 30 * DAY_IN_SECONDS );
            
            return $resultado;
        //Validación vatapp (Noruega, Suiza, Tailandia o Irlanda del Norte)
        } elseif ( in_array( $pais, $paises_vatapp, true ) || ( $pais === 'GB' && strpos( $nif, 'XI' ) !== false ) ) {
            $nif_clean  = strtoupper( preg_replace( "/[^\w]/i", '', $nif ) );
            $response   = wp_remote_post( 'https://vatapp.net/api/vat-ccf7f2e0', [
                'headers'   => [ 'Content-Type' => 'application/json' ],
                'body'      => json_encode( [ 'data' => $nif_clean ] ),
            ] );
            $partes     = json_split_objects( wp_remote_retrieve_body( $response ) );
            $eori       = json_decode( $partes[ 1 ] );
            $resultado  = isset( $eori->data->valid ) && $eori->data->valid == 1;
            //Guarda en caché
            set_transient( $cache_key, $resultado, 30 * DAY_IN_SECONDS );

            return $resultado;
        //Validación por SOAP para la UE
        } else {
            try {
                $soap       = new SoapClient( 'https://ec.europa.eu/taxation_customs/dds2/eos/validation/services/validation?wsdl' );
                $respuesta  = $soap->validateEORI( [ 'eori' => $nif ] );
                $resultado  = isset( $respuesta->return->result->statusDescr ) && $respuesta->return->result->statusDescr === 'Valid';
                //Guarda en caché
                set_transient( $cache_key, $resultado, 30 * DAY_IN_SECONDS );

                return $resultado;
            } catch ( SoapFault $e ) {
                return false;
            }
        }
    }
}
new APG_Campo_NIF_en_Pedido();

//Fuente: http://ryanuber.com/07-31-2012/split-and-decode-json-php.html
/**
 * json_split_objects - Return an array of many JSON objects
 *
 * In some applications (such as PHPUnit, or salt), JSON output is presented as multiple
 * objects, which you cannot simply pass in to json_decode(). This function will split
 * the JSON objects apart and return them as an array of strings, one object per indice.
 *
 * @param string $json  The JSON data to parse
 *
 * @return array
 */
function json_split_objects( $json ) {
    $q      = FALSE;
    $len    = strlen( $json );
    for ( $l = $c = $i = 0; $i < $len; $i++ ) {
        $json[ $i ] == '"' && ( $i > 0 ? $json[ $i - 1 ] : '' ) != '\\' && $q = !$q;
        if ( ! $q && in_array( $json[ $i ], array( " ", "\r", "\n", "\t" ) ) ) {
            continue;
        }
        in_array( $json[ $i ], array( '{', '[' ) ) && ! $q && $l++;
        in_array( $json[ $i ], array( '}', ']' ) ) && ! $q && $l--;
        ( isset( $objects[ $c ] ) && $objects[ $c ] .= $json[ $i ] ) || $objects[ $c ] = $json[ $i ];
        $c += ( $l == 0 );
    }
    
    return $objects;
}
