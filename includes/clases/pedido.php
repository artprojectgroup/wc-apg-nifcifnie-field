<?php
//Igual no deberías poder abrirme
defined( 'ABSPATH' ) || exit;
/*
use Automattic\WooCommerce\Blocks\Package;
use Automattic\WooCommerce\Blocks\Domain\Services\CheckoutFields;
*/
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
        
        //Valida el campo NIF/CIF/NIE
        if ( isset( $apg_nif_settings[ 'validacion' ] ) && $apg_nif_settings[ 'validacion' ] == "1" ) {
            add_action( 'woocommerce_checkout_process', [ $this, 'apg_nif_validacion_de_campo' ] );
            add_action( 'woocommerce_blocks_validate_location_address_fields', [ $this, 'apg_nif_validacion_de_campo_bloques' ], 10, 3 ); //Formulario de bloques
        }
        //Añade el número VIES
        if ( isset( $apg_nif_settings[ 'validacion_vies' ] ) && $apg_nif_settings[ 'validacion_vies' ] == "1" ) {
            if ( function_exists( 'has_block' ) && has_block( 'woocommerce/checkout', wc_get_page_id( 'checkout' ) ) ) {
                add_action( 'enqueue_block_assets', [ $this, 'apg_nif_carga_ajax' ] );
            } else {
                add_action( 'wp_enqueue_scripts', [ $this, 'apg_nif_carga_ajax' ] );
            }
            add_action( 'wp_ajax_nopriv_apg_nif_valida_VIES', [ $this, 'apg_nif_valida_VIES' ] );
            add_action( 'wp_ajax_apg_nif_valida_VIES', [ $this, 'apg_nif_valida_VIES' ] );
            //add_action( 'init', [ $this, 'apg_nif_quita_iva' ] );
            add_action( 'woocommerce_checkout_update_order_review', [ $this, 'apg_nif_quita_iva' ] );
            //add_action( 'woocommerce_before_calculate_totals', [ $this, 'apg_nif_quita_iva' ], 99 );
        }
        //Añade el número EORI
        if ( isset( $apg_nif_settings[ 'validacion_eori' ] ) && $apg_nif_settings[ 'validacion_eori' ] == "1" ) {
            if ( function_exists( 'has_block' ) && has_block( 'woocommerce/checkout', wc_get_page_id( 'checkout' ) ) ) {
                add_action( 'enqueue_block_assets', [ $this, 'apg_nif_carga_ajax' ], 20 );
            } else {
                add_action( 'wp_enqueue_scripts', [ $this, 'apg_nif_carga_ajax' ], 20 );
            }
            add_action( 'wp_ajax_nopriv_apg_nif_valida_EORI', [ $this, 'apg_nif_valida_EORI' ] );
            add_action( 'wp_ajax_apg_nif_valida_EORI', [ $this, 'apg_nif_valida_EORI' ] );
            add_action( 'woocommerce_checkout_process', [ $this, 'apg_nif_validacion_de_campo' ] );
            add_action( 'woocommerce_blocks_validate_location_address_fields', [ $this, 'apg_nif_validacion_de_campo_bloques' ], 10, 3 ); //Formulario de bloques
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
        
        $campos[ 'nif' ]    = [
            'label'         => $this->nombre_nif,
            'placeholder'   => $this->placeholder,
            'priority'      => $this->priority,
            'required'      => isset( $apg_nif_settings[ 'requerido' ] ) && $apg_nif_settings[ 'requerido' ] === '1',
        ];
        
        //Sólo es operativo en los checkout clásicos
        if ( ! WC_Blocks_Utils::has_block_in_page( wc_get_page_id( 'checkout' ), 'woocommerce/checkout' ) ) {
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
        }

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

    /** 
     * Valida el campo VAT number
     * Basado en JS validator de John Gardner: http://www.braemoor.co.uk/software/vat.shtml y https://github.com/mnestorov/regex-patterns
     */
    public function apg_nif_validacion_internacional( $vat_number, $vat_country, $pais ) {
        //Limpia el campo
        $vat_number     = preg_replace( '/[ -,.]/', '', $vat_number );
        //Comprueba el país a validar
        $valida_pais    = ( preg_match( "/^[a-zA-Z]+$/", $pais ) ) ? $pais : $vat_country;
        //Valida países específicos
        if ( $valida_pais == 'AR' ) { //Argentina
            return $this->apg_nif_valida_cuit( $vat_number );
        } else if ( $valida_pais == 'CL' ) { //Chile
            return $this->apg_nif_valida_rut( $vat_number );
        } else if ( $valida_pais == 'ES' ) { //España
            return $this->apg_nif_valida_nif( $vat_number );
        }
        //Comprueba la estructura del campo
        switch ( $valida_pais ) {
            case 'AL': //Albania 
                $eu_valido  = ( bool ) preg_match( '/^(AL)?J(\d{8}[A-Z])$/', $vat_number );
                break;
            case 'AT': //Austria 
                $eu_valido  = ( bool ) preg_match( '/^(AT)?U(\d{8})$/', $vat_number );
                break;
            case 'AX': //Islas de Åland
                $eu_valido  = ( bool ) preg_match( '/^(FI)?|(AX)?(\d{8})$/', $vat_number );
                break;
            case 'BE': //Bélgica 
                $eu_valido  = ( bool ) preg_match( '/(BE)?(0?\d{9})$/', $vat_number );
                break;
            case 'BG': //Bulgaria 
                $eu_valido  = ( bool ) preg_match( '/(BG)?(\d{9,10})$/', $vat_number );
                break;
            case 'BY': //Bielorusia 
                $eu_valido  = ( bool ) preg_match( '/(BY)?(\d{9})$/', $vat_number );
                break;
            case 'CH': //Suiza 
                $eu_valido  = ( bool ) preg_match( '/(CHE)?(\d{9})(MWST)?|(TVA)?|(IVA)?$/', $vat_number );
                break;
            case 'CY': //Chipre 
                $eu_valido  = ( bool ) preg_match( '/^(CY)?([0-5|9]\d{7}[A-Z])$/', $vat_number );
                break;
            case 'CZ': //República Checa
                $eu_valido  = ( bool ) preg_match( '/^(CZ)?(\d{8,10})(\d{3})?$/', $vat_number );
                break;
            case 'DE': //Alemania 
                $eu_valido  = ( bool ) preg_match( '/^(DE)?([1-9]\d{8,9})/', $vat_number );
                break;
            case 'DK': //Dinamarca 
                $eu_valido  = ( bool ) preg_match( '/^(DK)?(\d{8})$/', $vat_number );
                break;
            case 'EE': //Estonia 
                $eu_valido  = ( bool ) preg_match( '/^(EE)?(10\d{7,9})$/', $vat_number );
                break;
            case 'ES': //España 
                $eu_valido  = ( bool ) preg_match( '/^(ES)?([A-Z]\d{8})$/', $vat_number ) ||
                    preg_match( '/^(ES)?([A-H|N-S|W]\d{7}[A-J])$/', $vat_number ) ||
                    preg_match( '/^(ES)?([0-9|Y|Z]\d{7}[A-Z])$/', $vat_number ) ||
                    preg_match( '/^(ES)?([K|L|M|X]\d{7}[A-Z])$/', $vat_number );
                break;
            case 'EU': //Unión Europea 
                $eu_valido  = ( bool ) preg_match( '/^(EU)?(\d{9})$/', $vat_number );
                break;
            case 'FI': //Finlandia 
                $eu_valido  = ( bool ) preg_match( '/^(FI)?(\d{8})$/', $vat_number );
                break;
            case 'FO': //Islas Feroe
                $eu_valido  = ( bool ) preg_match( '/^(FO)?(\d{6})$/', $vat_number );
                break;
            case 'FR': //Francia 
                $eu_valido  = ( bool ) preg_match( '/^(FR)?(\d{11})$/', $vat_number ) ||
                    preg_match( '/^(FR)?([(A-H)|(J-N)|(P-Z)]\d{10})$/', $vat_number ) ||
                    preg_match( '/^(FR)?(\d[(A-H)|(J-N)|(P-Z)]\d{9})$/', $vat_number ) ||
                    preg_match( '/^(FR)?([(A-H)|(J-N)|(P-Z)]{2}\d{9})$/', $vat_number );
                break;
            case 'GB': //Gran Bretaña 
                $eu_valido  = ( bool ) preg_match( '/^(GB)?(\d{9})$/', $vat_number ) ||
                    preg_match( '/^(GB)?(\d{12})$/', $vat_number ) ||
                    preg_match( '/^(GB)?(GD\d{3})$/', $vat_number ) ||
                    preg_match( '/^(GB)?(HA\d{3})$/', $vat_number );
                break;
            case 'GR': //Grecia
                $eu_valido  = ( bool ) preg_match( '/^(GR)?(\d{8,9})$/', $vat_number ) ||
                    preg_match( '/^(EL)?(\d{9})$/', $vat_number );
                break;
            case 'HR': //Croacia 
                $eu_valido  = ( bool ) preg_match( '/^(HR)?(\d{11})$/', $vat_number );
                break;
            case 'HU': //Hungría 
                $eu_valido  = ( bool ) preg_match( '/^(HU)?(\d{8})$/', $vat_number );
                break;
            case 'IE': //Irlanda 
                $eu_valido  = ( bool ) preg_match( '/^(IE)?(\d{7}[A-W])$/', $vat_number ) ||
                    preg_match( '/^(IE)?([7-9][A-Z\*\+)]\d{5}[A-W])$/', $vat_number ) ||
                    preg_match( '/^(IE)?(\d{7}[A-W][AH])$/', $vat_number );
                break;
            case 'IS': //Islandia 
                $eu_valido  = ( bool ) preg_match( '/^(IS)?(\d{5,6})$/', $vat_number );
                break;
            case 'IT': //Italia 
                $eu_valido  = ( bool ) preg_match( '/^(IT)?(\d{11})$/', $vat_number );
                break;
            case 'LI': //Liechtenstein 
                $eu_valido  = ( bool ) preg_match( '/^(LI)?(\d{5})$/', $vat_number );
                break;
            case 'LT': //Lituania 
                $eu_valido  = ( bool ) preg_match( '/^(LT)?(\d{9}|\d{12})$/', $vat_number );
                break;
            case 'LU': //Luxemburgo 
                $eu_valido  = ( bool ) preg_match( '/^(LU)?(\d{8})$/', $vat_number );
                break;
            case 'LV': //Letonia 
                $eu_valido  = ( bool ) preg_match( '/^(LV)?(\d{11})$/', $vat_number );
                break;
            case 'MC': //Mónaco 
                $eu_valido  = ( bool ) preg_match( '/^(FR)?(\d[(A-H)|(J-N)|(P-Z)]\d{9})$/', $vat_number ) ||
                    preg_match( '/^(FR)?([(A-H)|(J-N)|(P-Z)]{2}\d{9})$/', $vat_number );
                break;
            case 'MD': //Moldavia 
                $eu_valido  = ( bool ) preg_match( '/^(MD)?(\d{8})$/', $vat_number );
                break;
            case 'ME': //Montenegro 
                $eu_valido  = ( bool ) preg_match( '/^(ME)?(\d{8})$/', $vat_number );
                break;
            case 'MK': //Macedonia del Norte 
                $eu_valido  = ( bool ) preg_match( '/^(MK)?(\d{13})$/', $vat_number );
                break;
            case 'MT': //Malta 
                $eu_valido  = ( bool ) preg_match( '/^(MT)?([1-9]\d{7,8})$/', $vat_number );
                break;
            case 'NL': //Países Bajos 
                $eu_valido  = ( bool ) preg_match( '/^(NL)?(\d{9})B\d{2}$/', $vat_number );
                break;
            case 'NO': //Noruega 
                $eu_valido  = ( bool ) preg_match( '/^(NO)?(\d{9})(MVA)?$/', $vat_number );
                break;
            case 'PL': //Polonia 
                $eu_valido  = ( bool ) preg_match( '/^(PL)?(\d{10})$/', $vat_number );
                break;
            case 'PT': //Portugal 
                $eu_valido  = ( bool ) preg_match( '/^(PT)?(\d{9})$/', $vat_number );
                break;
            case 'RO': //Rumanía 
                $eu_valido  = ( bool ) preg_match( '/^(RO)?([1-9]\d{2,10})$/', $vat_number );
                break;
            case 'RS': //Serbia 
                $eu_valido  = ( bool ) preg_match( '/^(RS)?(\d{9})$/', $vat_number );
                break;
            case 'SE': //Suecia 
                $eu_valido  = ( bool ) preg_match( '/^(SE)?(\d{10}01)$/', $vat_number );
                break;
            case 'SI': //Eslovenia 
                $eu_valido  = ( bool ) preg_match( '/^(SI)?([1-9]\d{7,8})$/', $vat_number );
                break;
            case 'SK': //República Eslovaca
                $eu_valido  = ( bool ) preg_match( '/^(SK)?([1-9]\d[(2-4)|(6-9)]\d{7})$/', $vat_number );
                break;
            case 'SM': //San Marino
                $eu_valido  = ( bool ) preg_match( '/^(SM)?(\d{5}$/', $vat_number );
                break;
            case 'UA': //Ucrania
                $eu_valido  = ( bool ) preg_match( '/^(UA)?(\d{12}$/', $vat_number );
                break;
            default:
                $eu_valido  = false;
        }

        return $eu_valido;
    }
    
    //Comprueba si el CUIT ingresado es válido (Argentina) - https://github.com/maurozadu/CUIT-Validator/blob/master/libs/cuit_validator.php
    public function apg_nif_valida_cuit( $cuit ) {
        $digits = [];
        if ( strlen( $cuit ) != 13 ) {
            return false;
        }
        for ( $i = 0; $i < strlen( $cuit ); $i++ ) {
            if ( $i == 2 or $i == 11 ) {
                if ( $cuit[ $i ] != '-' ) {
                    return false;
                }
            } else {
                if ( ! ctype_digit( $cuit[ $i ] ) ) {
                    return false;
                }
                if ( $i < 12 ) {
                    $digits[] = $cuit[ $i ];
                }
            }
        }
        $acum   = 0;
        foreach ( [ 5, 4, 3, 2, 7, 6, 5, 4, 3, 2 ] as $i => $multiplicador ) {
            $acum += $digits[ $i ] * $multiplicador;
        }
        $cmp    = 11 - ( $acum % 11 );
        if ( $cmp == 11 ) {
            $cmp    = 0;
        }
        if ( $cmp == 10 ) {
            $cmp    = 9;
        }
        return ( $cuit[ 12 ] == $cmp );
    }

    //Comprueba si el RUT ingresado es válido (Chile) - https://gist.github.com/punchi/3a5c44e7aa7ac0609ce9e53365572541
    public function apg_nif_valida_rut( $rut ) {
        if ( ! preg_match( "/^[0-9.]+[-]?+[0-9kK]{1}/", $rut ) ) {
            return false;
        }

        $rut    = preg_replace( '/[\.\-]/i', '', $rut );
        $dv     = substr( $rut, -1 );
        $numero = substr( $rut, 0, strlen( $rut ) - 1 );
        $i      = 2;
        $suma   = 0;
        foreach ( array_reverse( str_split( $numero ) ) as $v ) {
            if ( $i == 8 ) {
                $i = 2;
            }
            $suma += $v * $i;
            ++$i;
        }
        $dvr = 11 - ( $suma % 11 );

        if ( $dvr == 11 ) {
            $dvr = 0;
        }
        if ( $dvr == 10 ) {
            $dvr = 'K';
        }
        
        return ( $dvr == strtoupper( $dv ) ) ? true : false;       
    }

    //Valida el campo NIF/CIF/NIE (España)
    public function apg_nif_valida_nif( $nif ) {
        $nif_valido = false;
        $nif        = preg_replace( '/[ -,.]/', '', $nif );
        $nif        = str_replace( 'ES', '', $nif );

        for ( $i = 0; $i < 9; $i++ ) {
            $numero[ $i ]   = substr( $nif, $i, 1 );
        }

        if ( ! preg_match( '/((^[A-Z]{1}[0-9]{7}[A-Z0-9]{1}$|^[T]{1}[A-Z0-9]{8}$)|^[0-9]{8}[A-Z]{1}$)/', $nif ) ) { //No tiene formato válido
            return false;
        }

        if ( preg_match( '/(^[0-9]{8}[A-Z]{1}$)/', $nif ) ) {
            if ( $numero[ 8 ] == substr( 'TRWAGMYFPDXBNJZSQVHLCKE', substr( $nif, 0, 8 ) % 23, 1 ) ) { //NIF válido
                $nif_valido = true;
            }
        }

        $suma   = $numero[ 2 ] + $numero[ 4 ] + $numero[ 6 ];
        for ( $i = 1; $i < 8; $i += 2 ) {
            if ( 2 * $numero[ $i ] >= 10 ) {
                $suma   += substr( ( 2 * $numero[ $i ] ), 0, 1 ) + substr( ( 2 * $numero[ $i ] ), 1, 1 );
            } else {
                $suma   += 2 * $numero[ $i ];
            }
        }
        $suma_numero    = 10 - substr( $suma, strlen( $suma ) - 1, 1 );

        if ( preg_match( '/^[KLM]{1}/', $nif ) ) { //NIF especial válido
            if ( $numero[ 8 ] == chr( 64 + $suma_numero ) ) {
                $nif_valido = true;
            }
        }

        if ( preg_match( '/^[ABCDEFGHJNPQRSUVW]{1}/', $nif ) && isset( $numero[ 8 ] ) ) {
            if ( $numero[ 8 ] == chr( 64 + $suma_numero ) || $numero[ 8 ] == substr( $suma_numero, strlen( $suma_numero ) - 1, 1 ) ) { //CIF válido
                $nif_valido = true;
            }
        }

        if ( preg_match( '/^[T]{1}/', $nif ) ) {
            if ( $numero[ 8 ] == preg_match( '/^[T]{1}[A-Z0-9]{8}$/', $nif ) ) { //NIE válido (T)
                $nif_valido = true;
            }
        }

        if ( preg_match( '/^[XYZ]{1}/', $nif ) ) { //NIE válido (XYZ)
            if ( $numero[ 8 ] == substr( 'TRWAGMYFPDXBNJZSQVHLCKE', substr( str_replace( [ 'X', 'Y', 'Z' ], [ '0', '1', '2' ], $nif ), 0, 8 ) % 23, 1 ) ) {
                $nif_valido = true;
            }
        }

        return $nif_valido;
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

        //Validación
        if ( isset( $apg_nif_settings[ 'validacion' ] ) && $apg_nif_settings[ 'validacion' ] === '1' ) {
            $validacion = $this->apg_nif_valida_exencion( $billing_nif, $billing_country );

            //Mensaje de error para el formulario de facturación
            if ( ! $validacion[ 'usar_eori' ] && ! $validacion[ 'valido_vies' ] && ! $validacion[ 'vat_valido' ] ) {
                wc_add_notice( $this->mensaje_error, 'error' );
            }
            
            //Muestra el mensaje de error personalizado
            if ( apply_filters( 'apg_nif_display_error_message', false, $billing_nif, $billing_country ) ) {
                $mensaje = apply_filters( 'apg_nif_error_message', $this->mensaje_error, $billing_nif, $billing_country );
                wc_add_notice( $mensaje, 'error' );
            }

            $validacion_envio   = $this->apg_nif_valida_exencion( $shipping_nif, $shipping_country );
            //Mensaje de error para el formulario de envío
            // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'get-customer-details'
            if ( ! $validacion_envio[ 'usar_eori' ] && ! $validacion_envio[ 'valido_vies' ] && ! $validacion_envio[ 'vat_valido' ] && ! empty( $_POST[ 'ship_to_different_address' ] ) ) {
                wc_add_notice( $this->mensaje_error . ' - ' . esc_attr__( 'Shipping details', 'wc-apg-nifcifnie-field' ), 'error' );
            }

        }

        //Validación EORI
        if ( isset( $apg_nif_settings[ 'validacion_eori' ] ) && $apg_nif_settings[ 'validacion_eori' ] === '1' ) {
            //Muestra el mensaje de error EORI
            if ( $validacion[ 'usar_eori' ] && ! $validacion[ 'valido_eori' ] ) {
                wc_add_notice( $this->mensaje_eori, 'error' );
            }
        }
    }

    //Valida el campo NIF/CIF/NIE - Bloques
    public function apg_nif_validacion_de_campo_bloques( WP_Error $errors, $fields, $group ) {
        global $apg_nif_settings;

        if ( ! is_checkout() && !( defined( 'WOOCOMMERCE_CHECKOUT' ) && WOOCOMMERCE_CHECKOUT ) && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            return;
        }

        if ( ! WC_Blocks_Utils::has_block_in_page( wc_get_page_id( 'checkout' ), 'woocommerce/checkout' ) ) {
            return;
        }

        //Procesa los camops
        $nif        = isset( $fields[ 'apg/nif' ] ) ? sanitize_text_field( $fields[ 'apg/nif' ] ) : '';
        $pais       = isset( $fields[ 'country' ] ) ? sanitize_text_field( $fields[ 'country' ] ) : '';

        if ( isset( $apg_nif_settings[ 'validacion' ] ) && $apg_nif_settings[ 'validacion' ] === '1' ) {
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
                //Muestra el mensaje de error EORI
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

        //Funcional si estamos en el checkout
        if ( ! is_checkout() ) {
            return;
        }

        //Detección segura del tipo de checkout
        $is_blocks_checkout = function_exists( 'has_block' ) && has_block( 'woocommerce/checkout', wc_get_page_id( 'checkout' ) );

        //Bloque de Finalizar Compra
        if ( $is_blocks_checkout ) {
            if ( ! wp_script_is( 'apg_nif_valida_bloques', 'enqueued' ) ) {
                wp_enqueue_script( 'apg_nif_valida_bloques', plugin_dir_url( DIRECCION_apg_nif ) . 'assets/js/valida-bloques-nif.js', [ 'jquery' ], VERSION_apg_nif, true );
                wp_localize_script( 'apg_nif_valida_bloques', 'apg_nif_ajax', [
                    'url' => admin_url( 'admin-ajax.php' ),
                    'error' => $this->mensaje_vies,
                    'max' => $this->mensaje_max,
                ] );
                wp_localize_script( 'apg_nif_valida_bloques', 'apg_nif_eori_ajax', [
                    'url' => admin_url( 'admin-ajax.php' ),
                    'error' => $this->mensaje_eori,
                    'lista' => $apg_nif_settings[ 'eori_paises' ] ?? [],
                ] );
            }
        //Finalizr Compra Clásico
        } else {
            if ( ! wp_script_is( 'apg_nif_valida', 'enqueued' ) ) {
                wp_enqueue_script( 'apg_nif_valida', plugin_dir_url( DIRECCION_apg_nif ) . 'assets/js/valida-nif.js', [ 'jquery' ], VERSION_apg_nif, true );
                wp_localize_script( 'apg_nif_valida', 'apg_nif_ajax', [
                    'url' => admin_url( 'admin-ajax.php' ),
                    'error' => $this->mensaje_vies,
                    'max' => $this->mensaje_max,
                ] );
                wp_localize_script( 'apg_nif_valida', 'apg_nif_eori_ajax', [
                    'url' => admin_url( 'admin-ajax.php' ),
                    'error' => $this->mensaje_eori,
                    'lista' => $apg_nif_settings[ 'eori_paises' ] ?? [],
                ] );
            }
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
        $vat_valido     = $this->apg_nif_validacion_internacional( $nif, $pais_cliente, $prefijo_nif );
        
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
    
    //Valida el campo VIES
    public function apg_nif_valida_VIES() {
        global $apg_nif_settings;

        //No funciona en el panel de administración (excepto AJAX)
        if ( is_admin() && ! wp_doing_ajax() ) { 
            return;
        }

        //Procesa los camops
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'get-customer-details'
        $pais                   = isset( $_POST[ 'billing_country' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'billing_country' ] ) ) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'get-customer-details'
        $billing_nif            = isset( $_POST[ 'billing_nif' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'billing_nif' ] ) ) : '';

        $resultado              = $this->apg_nif_valida_exencion( $billing_nif, $pais );
        WC()->session->set( 'apg_nif', $resultado[ 'es_exento' ] );
        wp_send_json_success( $resultado );
    }
    
    //Valida el campo EORI
    public function apg_nif_valida_EORI() {
        global $apg_nif_settings;

        //No funciona en el panel de administración (excepto AJAX)
        if ( is_admin() && ! wp_doing_ajax() ) { 
            return;
        }

        //Procesa los camops
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'get-customer-details'
        $pais                   = isset( $_POST[ 'billing_country' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'billing_country' ] ) ) : '';
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- WooCommerce already validates nonce via 'get-customer-details'
        $billing_nif            = isset( $_POST[ 'billing_nif' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'billing_nif' ] ) ) : '';

        $resultado              = $this->apg_nif_valida_exencion( $billing_nif, $pais );
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
