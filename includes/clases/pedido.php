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
            $mensaje_eori;

    //Inicializa las acciones de Pedido
    public function __construct() {
        global $apg_nif_settings;
        
        add_filter( 'woocommerce_default_address_fields', [ $this, 'apg_nif_campos_de_direccion' ] );
        add_filter( 'woocommerce_billing_fields', [ $this, 'apg_nif_formulario_de_facturacion' ] );
        add_filter( 'woocommerce_shipping_fields', [ $this, 'apg_nif_formulario_de_envio' ] );
        add_action( 'after_setup_theme', [ $this, 'apg_nif_traducciones' ] );
        //Valida el campo NIF/CIF/NIE
        if ( isset( $apg_nif_settings[ 'validacion' ] ) && $apg_nif_settings[ 'validacion' ] == "1" ) {
            add_action( 'woocommerce_checkout_process', [ $this, 'apg_nif_validacion_de_campo' ] );
        }
        //Añade el número VIES
        if ( isset( $apg_nif_settings[ 'validacion_vies' ] ) && $apg_nif_settings[ 'validacion_vies' ] == "1" ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'apg_nif_carga_ajax' ] );
            add_action( 'wp_ajax_nopriv_apg_nif_valida_VIES', [ $this, 'apg_nif_valida_VIES' ] );
            add_action( 'wp_ajax_apg_nif_valida_VIES', [ $this, 'apg_nif_valida_VIES' ] );
            add_action( 'init', [ $this, 'apg_nif_quita_iva' ] );
        }
        //Añade el número EORI
        if ( isset( $apg_nif_settings[ 'validacion_eori' ] ) && $apg_nif_settings[ 'validacion_eori' ] == "1" ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'apg_nif_carga_ajax' ] );
            add_action( 'wp_ajax_nopriv_apg_nif_valida_EORI', [ $this, 'apg_nif_valida_EORI' ] );
            add_action( 'wp_ajax_apg_nif_valida_EORI', [ $this, 'apg_nif_valida_EORI' ] );
            add_action( 'woocommerce_checkout_process', [ $this, 'apg_nif_validacion_de_campo' ] );
        }
    }

    //Añade las traducciones
    public function apg_nif_traducciones() {
        global $apg_nif_settings;

        $this->nombre_nif       = __( ( isset( $apg_nif_settings[ 'etiqueta' ] ) ? esc_attr( $apg_nif_settings[ 'etiqueta' ] ) : 'NIF/CIF/NIE' ), 'wc-apg-nifcifnie-field' ); //Nombre original del campo
        $this->placeholder      = _x( ( isset( $apg_nif_settings[ 'placeholder' ] ) ? esc_attr( $apg_nif_settings[ 'placeholder' ] ) : 'NIF/CIF/NIE number' ), 'placeholder', 'wc-apg-nifcifnie-field' ); //Nombre original del placeholder
        $this->mensaje_error    = __( ( isset( $apg_nif_settings[ 'error' ] ) ? esc_attr( $apg_nif_settings[ 'error' ] ) : 'Please enter a valid NIF/CIF/NIE.' ), 'wc-apg-nifcifnie-field' ); //Mensaje de error
        $this->priority         = ( isset( $apg_nif_settings[ 'prioridad' ] ) ? esc_attr( $apg_nif_settings[ 'prioridad' ] ) : 31 ); //Prioridad del campo
                
        //Número VIES
        if ( isset( $apg_nif_settings[ 'validacion_vies' ] ) && $apg_nif_settings[ 'validacion_vies' ] == "1" ) {
            $this->nombre_nif   = __( ( isset( $apg_nif_settings[ 'etiqueta_vies' ] ) ? esc_attr( $apg_nif_settings[ 'etiqueta_vies' ] ) : 'NIF/CIF/NIE/VAT number' ), 'wc-apg-nifcifnie-field' ); //Nombre modificado del campo
            $this->placeholder  = _x( ( isset( $apg_nif_settings[ 'placeholder_vies' ] ) ? esc_attr( $apg_nif_settings[ 'placeholder_vies' ] ) : 'NIF/CIF/NIE/VAT number' ), 'placeholder', 'wc-apg-nifcifnie-field' ); //Nombre modificado del placeholder
            $this->mensaje_vies = __( ( isset( $apg_nif_settings[ 'error_vies' ] ) ? esc_attr( $apg_nif_settings[ 'error_vies' ] ) : 'Please enter a valid VIES VAT number.' ), 'wc-apg-nifcifnie-field' ); //Mensaje de error
        }
        //Número EORI
        if ( isset( $apg_nif_settings[ 'validacion_eori' ] ) && $apg_nif_settings[ 'validacion_eori' ] == "1" ) {
            $this->nombre_nif   = __( ( isset( $apg_nif_settings[ 'etiqueta_eori' ] ) ? esc_attr( $apg_nif_settings[ 'etiqueta_eori' ] ) : 'NIF/CIF/NIE/EORI number' ), 'wc-apg-nifcifnie-field' ); //Nombre modificado del campo
            $this->placeholder  = _x( ( isset( $apg_nif_settings[ 'placeholder_eori' ] ) ? esc_attr( $apg_nif_settings[ 'placeholder_eori' ] ) : 'NIF/CIF/NIE/EORI number' ), 'placeholder', 'wc-apg-nifcifnie-field' ); //Nombre modificado del placeholder
            $this->mensaje_eori = __( ( isset( $apg_nif_settings[ 'error_eori' ] ) ? esc_attr( $apg_nif_settings[ 'error_eori' ] ) : 'Please enter a valid EORI number.' ), 'wc-apg-nifcifnie-field' ); //Mensaje de error
        }
    }

    //Arregla la dirección predeterminada
    public function apg_nif_campos_de_direccion( $campos ) {
        $campos[ 'nif' ]    = [
            'label'         => $this->nombre_nif,
            'placeholder'   => $this->placeholder,
            'priority'      => $this->priority,
        ];
        //Añade el correo electónico y el teléfono
        $campos[ 'email' ]  = [
            'label'         => __( 'Email Address', 'woocommerce' ),
            'required'      => true,
            'type'          => 'email',
            'validate'      => [
                'email'
            ],
            'autocomplete'  => 'email username',
            'priority'      => 110,
        ];
        $campos[ 'phone' ]  = [
            'label'         => __( 'Phone', 'woocommerce' ),
            'required'      => true,
            'type'          => 'tel',
            'validate'      => [
                'phone'
            ],
            'autocomplete'  => 'tel',
            'priority'      => 100,
        ];

        //Fuerza la actualización del checkout con el código postal y la provincia/estado
        $campos[ 'postcode' ][ 'class' ][]  = 'update_totals_on_change';
        $campos[ 'state' ][ 'class' ][]     = 'update_totals_on_change';

        return $campos;
    }

    //Arreglamos el formulario de facturación
    function apg_nif_formulario_de_facturacion( $campos ) {
        global $apg_nif_settings;

        $campos[ 'billing_nif' ][ 'required' ]  = ( isset( $apg_nif_settings[ 'requerido' ] ) && $apg_nif_settings[ 'requerido' ] == "1" ) ? true : false;

        return $campos;
    }

    //Arregla el formulario de envío
    public function apg_nif_formulario_de_envio( $campos ) {
        global $apg_nif_settings;

        $facturacion    = WC()->countries->get_address_fields( WC()->countries->get_base_country(), 'billing_' );

        $campos[ 'shipping_nif' ][ 'required' ]     = ( isset( $apg_nif_settings[ 'requerido_envio' ] ) && $apg_nif_settings[ 'requerido_envio' ] == "1" ) ? true : false;
        $campos[ 'shipping_email' ][ 'priority' ]   = $facturacion[ 'billing_email' ][ 'priority' ];
        $campos[ 'shipping_phone' ][ 'priority' ]   = $facturacion[ 'billing_phone' ][ 'priority' ];

        return $campos;
    }

    //Valida el campo NIF/CIF/NIE
    public function apg_nif_validacion( $nif ) {
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

    /** 
     * Valida el campo VAT number
     * Basado en JS validator de John Gardner: http://www.braemoor.co.uk/software/vat.shtml 
     */
    public static function apg_nif_validacion_eu( $vat_number ) {
        $vat_number = preg_replace( '/[ -,.]/', '', $vat_number );
        if ( strlen( $vat_number ) < 8 ) {
            return false;
        }
        switch ( substr( $vat_number, 0, 2 ) ) {
            case 'AT': //AUSTRIA 
                $eu_valido  = ( bool )preg_match( '/^(AT)U(\d{8})$/', $vat_number );
                break;
            case 'BE': //BÉLGICA 
                $eu_valido  = ( bool )preg_match( '/(BE)(0?\d{9})$/', $vat_number );
                break;
            case 'BG': //BULGARIA 
                $eu_valido  = ( bool )preg_match( '/(BG)(\d{9,10})$/', $vat_number );
                break;
            case 'CH': //SUIZA 
                $eu_valido  = ( bool )preg_match( '/(CHE)(\d{9})(MWST)?$/', $vat_number );
                break;
            case 'CY': //CHIPRE 
                $eu_valido  = ( bool )preg_match( '/^(CY)([0-5|9]\d{7}[A-Z])$/', $vat_number );
                break;
            case 'CZ': //REPÚBLICA CHECA
                $eu_valido  = ( bool )preg_match( '/^(CZ)(\d{8,10})(\d{3})?$/', $vat_number );
                break;
            case 'DE': //ALEMANIA 
                $eu_valido  = ( bool )preg_match( '/^(DE)([1-9]\d{8,9})/', $vat_number );
                break;
            case 'DK': //DINAMARCA 
                $eu_valido  = ( bool )preg_match( '/^(DK)(\d{8})$/', $vat_number );
                break;
            case 'EE': //ESTONIA 
                $eu_valido  = ( bool )preg_match( '/^(EE)(10\d{7,9})$/', $vat_number );
                break;
            case 'EL': //GRECIA 
                $eu_valido  = ( bool )preg_match( '/^(EL)(\d{9})$/', $vat_number );
                break;
            case 'ES': //ESPAÑA 
                $eu_valido  = ( bool )preg_match( '/^(ES)([A-Z]\d{8})$/', $vat_number ) ||
                    preg_match( '/^(ES)([A-H|N-S|W]\d{7}[A-J])$/', $vat_number ) ||
                    preg_match( '/^(ES)([0-9|Y|Z]\d{7}[A-Z])$/', $vat_number ) ||
                    preg_match( '/^(ES)([K|L|M|X]\d{7}[A-Z])$/', $vat_number );
                break;
            case 'EU': //UNIÓN EUROPEA 
                $eu_valido  = ( bool )preg_match( '/^(EU)(\d{9})$/', $vat_number );
                break;
            case 'FI': //FINLANDIA 
                $eu_valido  = ( bool )preg_match( '/^(FI)(\d{8})$/', $vat_number );
                break;
            case 'FR': //FRANCIA 
                $eu_valido  = ( bool )preg_match( '/^(FR)(\d{11})$/', $vat_number ) ||
                    preg_match( '/^(FR)([(A-H)|(J-N)|(P-Z)]\d{10})$/', $vat_number ) ||
                    preg_match( '/^(FR)(\d[(A-H)|(J-N)|(P-Z)]\d{9})$/', $vat_number ) ||
                    preg_match( '/^(FR)([(A-H)|(J-N)|(P-Z)]{2}\d{9})$/', $vat_number );
                break;
            case 'GB': //GRAN BRETAÑA 
                $eu_valido  = ( bool )preg_match( '/^(GB)?(\d{9})$/', $vat_number ) ||
                    preg_match( '/^(GB)?(\d{12})$/', $vat_number ) ||
                    preg_match( '/^(GB)?(GD\d{3})$/', $vat_number ) ||
                    preg_match( '/^(GB)?(HA\d{3})$/', $vat_number );
                break;
            case 'GR': //GRECIA
                $eu_valido  = ( bool )preg_match( '/^(GR)(\d{8,9})$/', $vat_number );
                break;
            case 'HR': //CROACIA 
                $eu_valido  = ( bool )preg_match( '/^(HR)(\d{11})$/', $vat_number );
                break;
            case 'HU': //HUNGRÍA 
                $eu_valido  = ( bool )preg_match( '/^(HU)(\d{8})$/', $vat_number );
                break;
            case 'IE': //IRLANDA 
                $eu_valido  = ( bool )preg_match( '/^(IE)(\d{7}[A-W])$/', $vat_number ) ||
                    preg_match( '/^(IE)([7-9][A-Z\*\+)]\d{5}[A-W])$/', $vat_number ) ||
                    preg_match( '/^(IE)(\d{7}[A-W][AH])$/', $vat_number );
                break;
            case 'IT': //ITALIA 
                $eu_valido  = ( bool )preg_match( '/^(IT)(\d{11})$/', $vat_number );
                break;
            case 'LV': //LETONIA 
                $eu_valido  = ( bool )preg_match( '/^(LV)(\d{11})$/', $vat_number );
                break;
            case 'LT': //LITUANIA 
                $eu_valido  = ( bool )preg_match( '/^(LT)(\d{9}|\d{12})$/', $vat_number );
                break;
            case 'LU': //LUXEMBURGO 
                $eu_valido  = ( bool )preg_match( '/^(LU)(\d{8})$/', $vat_number );
                break;
            case 'MT': //MALTA 
                $eu_valido  = ( bool )preg_match( '/^(MT)([1-9]\d{7,8})$/', $vat_number );
                break;
            case 'NL': //PAÍSES BAJOS 
                $eu_valido  = ( bool )preg_match( '/^(NL)(\d{9})B\d{2}$/', $vat_number );
                break;
            case 'NO': //NORUEGA 
                $eu_valido  = ( bool )preg_match( '/^(NO)(\d{9})$/', $vat_number );
                break;
            case 'PL': //POLONIA 
                $eu_valido  = ( bool )preg_match( '/^(PL)(\d{10})$/', $vat_number );
                break;
            case 'PT': //PORTUGAL 
                $eu_valido  = ( bool )preg_match( '/^(PT)(\d{9})$/', $vat_number );
                break;
            case 'RO': //RUMANÍA 
                $eu_valido  = ( bool )preg_match( '/^(RO)([1-9]\d{2,10})$/', $vat_number );
                break;
            case 'RS': //SERBIA 
                $eu_valido  = ( bool )preg_match( '/^(RS)(\d{9})$/', $vat_number );
                break;
            case 'SI': //ESLOVENIA 
                $eu_valido  = ( bool )preg_match( '/^(SI)([1-9]\d{7,8})$/', $vat_number );
                break;
            case 'SK': //REPÚBLICA ESLOVACA
                $eu_valido  = ( bool )preg_match( '/^(SK)([1-9]\d[(2-4)|(6-9)]\d{7})$/', $vat_number );
                break;
            case 'SE': //SUECIA 
                $eu_valido  = ( bool )preg_match( '/^(SE)(\d{10}01)$/', $vat_number );
                break;
            default:
                $eu_valido  = false;
        }

        return $eu_valido;
    }

    //Valida el campo NIF/CIF/NIE
    public function apg_nif_validacion_de_campo() {
        global $apg_nif_settings;
        
        $facturacion    = true;
        $envio          = true;
        $pais           = strtoupper( substr( $_POST[ 'billing_nif' ], 0, 2 ) );

        //Comprueba si es un número VIES válido
        if ( $pais == $_POST[ 'billing_country' ] ) {
            $facturacion    = $this->apg_nif_validacion_eu( strtoupper( $_POST[ 'billing_nif' ] ) );
        }

        //Comprueba el formulario de facturación
        if ( $_POST[ 'billing_country' ] == "ES" && isset( $_POST[ 'billing_nif' ] ) ) {
            $facturacion    = $this->apg_nif_validacion( strtoupper( $_POST[ 'billing_nif' ] ) );
        }

        //Comprueba el formulario de envío
        if ( $_POST[ 'shipping_country' ] == "ES" && isset( $_POST[ 'shipping_nif' ] ) ) {
            $envio          = $this->apg_nif_validacion( strtoupper( $_POST[ 'shipping_nif' ] ) );
        }
        
        //Muestra el mensaje de error
        if ( ! $facturacion || ! $envio ) {
            //Mensaje de error para el formulario de facturación
            if ( ! $facturacion && ! empty( $_POST[ 'billing_nif' ] ) ) {
                wc_add_notice( $this->mensaje_error, 'error' );
            }
            //Mensaje de error para el formulario de envío
            if ( ! $envio && ! empty( $_POST[ 'shipping_nif' ] ) && $_POST[ 'ship_to_different_address' ] ) {
                wc_add_notice( $this->mensaje_error . ' - ' . __( 'Shipping details', 'woocommerce' ), 'error' );
            }
        }
        
        //Muestra el mensaje de error EORI
        if ( isset( $apg_nif_settings[ 'eori_paises' ] ) && in_array( $_POST[ 'billing_country' ], $apg_nif_settings[ 'eori_paises' ] ) && ! $_SESSION[ 'apg_eori' ] ) {
            wc_add_notice( $this->mensaje_eori, 'error' );
        }
        
        //Muestra el mensaje de error personalizado
        if ( apply_filters( "apg_nif_display_error_message", false, $_POST[ 'billing_nif' ], $_POST[ 'billing_country' ] ) ) {
            wc_add_notice( apply_filters( "apg_nif_error_message", $this->mensaje_error, $_POST[ 'billing_nif' ], $_POST[ 'billing_country' ] ), 'error' );
        }
    }

    //Carga el JavaScript necesario
    public function apg_nif_carga_ajax() {
        global $apg_nif_settings;
        
        if ( is_checkout() ) {
            //Añade el número VIES
            if ( isset( $apg_nif_settings[ 'validacion_vies' ] ) && $apg_nif_settings[ 'validacion_vies' ] == "1" ) {
                wp_enqueue_script( 'apg_nif_vies', plugin_dir_url( DIRECCION_apg_nif ) . '/assets/js/valida-vies.js', [ 'jquery' ] );
                wp_localize_script( 'apg_nif_vies', 'apg_nif_ajax', [
                    'url'   => admin_url( 'admin-ajax.php' ),
                    'error' => $this->mensaje_vies,
                ] );
            }
            //Añade el número EORI
            if ( isset( $apg_nif_settings[ 'validacion_eori' ] ) && $apg_nif_settings[ 'validacion_eori' ] == "1" && isset( $apg_nif_settings[ 'eori_paises' ] ) ) {
                wp_enqueue_script( 'apg_nif_eori', plugin_dir_url( DIRECCION_apg_nif ) . '/assets/js/valida-eori.js', [ 'jquery' ] );
                wp_localize_script( 'apg_nif_eori', 'apg_nif_eori_ajax', [
                    'url'   => admin_url( 'admin-ajax.php' ),
                    'error' => $this->mensaje_eori,
                    'lista' => $apg_nif_settings[ 'eori_paises' ],
                ] );
            }
        }
    }

    //Valida el campo VIES
    public function apg_nif_valida_VIES() {
        global $apg_nif_settings;

        if ( is_admin() && ! wp_doing_ajax() ) { //No funciona en el panel de administración
            return;
        }
        
        if ( isset( $_POST[ 'billing_country' ] ) && isset( $apg_nif_settings[ 'eori_paises' ] ) && in_array( $_POST[ 'billing_country' ], $apg_nif_settings[ 'eori_paises' ] ) ) { //Sólo si no está incluido en el listado de países EORI
            echo true;
            
            return;
        }


        $_SESSION[ 'apg_nif' ]  = false;
        $valido                 = true;
        $iso_vies               = [ //Hack para Grecia
            'EL' => 'GR',
        ];
        $paises_validos         = [ //Listado de países válidos
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

        if ( isset( $_POST[ 'billing_country' ] ) && $_POST[ 'billing_country' ] != WC()->countries->get_base_country() && in_array( $_POST[ 'billing_country' ], $paises_validos ) ) { //Sólo si el país es distinto al de la tienda y pertenece al listado de países válidos
            if ( isset( $_POST[ 'billing_nif' ] ) && $_POST[ 'billing_nif' ] ) {
                //Separa el país del VIES
                $pais = strtoupper( substr( $_POST[ 'billing_nif' ], 0, 2 ) );
                if ( ! empty( $pais ) && isset( $iso_vies[ $pais ] ) ) { //Hack para Irlanda y Grecia
                    $pais   = $iso_vies[ $pais ];
                }
                if ( $pais == $_POST[ 'billing_country' ] ) { //El VIES incluye el prefijo
                    $nif    = substr( $_POST[ 'billing_nif' ], 2 );
                } else {
                    $pais   = $_POST[ 'billing_country' ];
                    $nif    = $_POST[ 'billing_nif' ];
                }
                if ( array_search( $pais, $iso_vies ) ) { //Hack para Irlanda y Grecia
                    $pais = array_search( $pais, $iso_vies );
                }

                //Comprueba el VIES
                $validacion = new SoapClient( "https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl" );

                if ( $validacion ) {
                    $parametros = [
                        'countryCode'   => $pais,
                        'vatNumber'     => $nif
                    ];
                    try {
                        $respuesta  = $validacion->checkVat( $parametros );
                        $valido     = ( $respuesta->valid == true ) ? true : false;
                    } catch ( SoapFault $e ) {
                        $valido     = false;
                    }
                } else {
                    $valido = false;
                }
                if ( $valido ) {
                    $_SESSION[ 'apg_nif' ]  = true;
                }
            }
        }
        
        echo $valido;
        
        return;
    }

    //Quita impuestos a VIES válido
    public function apg_nif_quita_iva( $carro ) {
        if ( is_checkout() || defined( 'WOOCOMMERCE_CHECKOUT' ) || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            if ( ! session_id() ) {
                session_start();
            }
            if ( isset( $_SESSION[ 'apg_nif' ] ) ) {
                WC()->customer->set_is_vat_exempt( $_SESSION[ 'apg_nif' ] );
            }
        }
    }

    //Valida el campo EORI
    public function apg_nif_valida_EORI() {
        global $apg_nif_settings;
        
        //No funciona en el panel de administración
        if ( is_admin() && ! wp_doing_ajax() ) {
            return;
        }

        //Variables
        $_SESSION[ 'apg_eori' ] = false;
        $valido                 = false;
        //Hack para Grecia
        $iso_vies               = [
            'EL' => 'GR',
        ];
        
        //Añade el código del país al EORI en caso de que no lo incluya
        if ( isset( $_POST[ 'billing_country' ] ) && $_POST[ 'billing_country' ] != WC()->countries->get_base_country() ) {
            if ( isset( $_POST[ 'billing_nif' ] ) && $_POST[ 'billing_nif' ] ) {
                //Comprueba el país
                $pais = $_POST[ 'billing_country' ];
                if ( ! empty( $pais ) && isset( $iso_vies[ $pais ] ) ) { //Hack para Irlanda y Grecia
                    $pais   = $iso_vies[ $pais ];
                }
                if ( array_search( $pais, $iso_vies ) ) {
                    $pais = array_search( $pais, $iso_vies );
                }
                
                $nif    = ( ! $pais == $_POST[ 'billing_country' ] ) ? $pais . $_POST[ 'billing_nif' ] : $_POST[ 'billing_nif' ];
            }
        }
        
        //Valida el EORI
        if ( isset( $_POST[ 'billing_country' ] ) && $_POST[ 'billing_country' ] != WC()->countries->get_base_country() && isset( $apg_nif_settings[ 'eori_paises' ] ) && in_array( $_POST[ 'billing_country' ], $apg_nif_settings[ 'eori_paises' ] ) ) { //Sólo si el país es distinto al de la tienda y pertenece al listado de países de validación EORI
            //Listado de países para validar en https://vatapp.net
            $paises = [
                'NO', 
                'CH', 
                'TH',
            ];
            //Reino Unido
            if ( $_POST[ 'billing_country' ] == 'GB' && isset( $_POST[ 'billing_nif' ] ) && $_POST[ 'billing_nif' ] && strpos( $_POST[ 'billing_nif' ], 'XI' ) === false ) {
                $argumentos[ 'headers' ]	= [
                    'Content-Type'				=> 'application/json',
                ];
                $argumentos[ 'body' ]		= json_encode( [ 
                    'eoris' => [ $_POST[ 'billing_nif' ] ] 
                ] );

                $respuesta					= wp_remote_post( "https://api.service.hmrc.gov.uk/customs/eori/lookup/check-multiple-eori", $argumentos );
                $eori                       = json_decode( wp_remote_retrieve_body( $respuesta ) );
                $valido                     = ( isset( $eori[ 0 ]->valid ) && $eori[ 0 ]->valid == true ) ? true : false; 
            }
            //Noruega, Suiza, Tailandia o Irlanda del Norte
            else if ( ( in_array( $_POST[ 'billing_country' ], $paises ) && isset( $_POST[ 'billing_nif' ] ) && $_POST[ 'billing_nif' ] ) || ( $_POST[ 'billing_country' ] == 'GB' && strpos( $_POST[ 'billing_nif' ], 'XI' ) !== false ) ) {
                $argumentos[ 'headers' ]	= [
                    'Content-Type'				=> 'application/json',
                ];
                $nif                        = strtoupper( preg_replace( "/[^\w]/i", "", $_POST[ 'billing_nif' ] ) );
                $argumentos[ 'body' ]		= json_encode( [ 
                    'data'  => $nif 
                ] );

                $respuesta					= wp_remote_post( "https://vatapp.net/api/vat-ccf7f2e0", $argumentos );
                $eori                       = json_split_objects( wp_remote_retrieve_body( $respuesta ) );
                $eori                       = json_decode( $eori[1] );
                $valido                     = ( isset( $eori->data->valid ) && $eori->data->valid == 1 ) ? true : false;
            }
            //Unión Europea
            else if ( isset( $_POST[ 'billing_nif' ] ) && $_POST[ 'billing_nif' ] ) {
                $validacion = new SoapClient( "https://ec.europa.eu/taxation_customs/dds2/eos/validation/services/validation?wsdl" );

                if ( $validacion ) {
                    $parametros = [
                        'eori'     => $_POST[ 'billing_nif' ]
                    ];
                    try {
                        $respuesta  = $validacion->validateEORI( $parametros );
                        $valido     = ( $respuesta->return->result->statusDescr == 'Valid' ) ? true : false;
                    } catch ( SoapFault $e ) {
                        $valido     = false;
                    }
                } else {
                    $valido = false;
                }
            }

            //Almacena y devuelve el valor
            if ( $valido ) {
                $_SESSION[ 'apg_eori' ]  = true;
            }
            echo $valido;

            return;
        }
        //Devuelve el valor
        echo $valido;
        
        return;
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
