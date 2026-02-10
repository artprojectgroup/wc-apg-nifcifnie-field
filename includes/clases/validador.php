<?php
/**
 * Validaciones específicas de identificadores fiscales por país.
 *
 * Proporciona funciones para validar NIF/VAT/CUIT/RUT, usadas por el
 * flujo de checkout del plugin (archivo `pedido.php`). Algunas validaciones
 * implementan algoritmos oficiales con dígitos de control; otras solo
 * verifican el patrón mediante expresiones regulares.
 *
 * @package WC_APG_NIFCIFNIE_Field
 */

// Igual no deberías poder abrirme.
defined( 'ABSPATH' ) || exit;

/**
 * Comprueba si un CUIT argentino es válido (11 dígitos con dígito verificador).
 *
 * Limpia no dígitos, verifica longitud y calcula el dígito de control
 * con los multiplicadores oficiales.
 *
 * @param string $vat CUIT a validar (con o sin separadores).
 * @return bool      true si es válido; false en caso contrario.
 *
 * @see https://github.com/maurozadu/CUIT-Validator/blob/master/libs/cuit_validator.php
 */
function apg_nif_valida_ar( string $vat ): bool {
    // Limpia guiones, espacios y puntos.
    $vat    = preg_replace( '/[^\d]/', '', $vat );
    $len    = strlen( $vat );

    // 1) DNI: 7 u 8 dígitos numéricos (sin checksum)
    if ( $len >= 7 && $len <= 8 && ctype_digit( $vat ) ) {
        return true;
    }

    // 2) CUIT: exactamente 11 dígitos con dígito verificador
    if ( $len !== 11 || ! ctype_digit( $vat ) ) {
        return false;
    }

    // Extrae dígitos y calcula verificador.
    $mult   = array( 5, 4, 3, 2, 7, 6, 5, 4, 3, 2 );
    $suma   = 0;
    for ( $i = 0; $i < 10; $i++ ) {
    $suma   += (int) $vat[ $i ] * $mult[ $i ];
    }
    $resto          = $suma % 11;
    $verificador    = 11 - $resto;
    if ( 11 === $verificador ) {
        $verificador = 0;
    }
    if ( 10 === $verificador ) {
        $verificador = 9;
    }

    return (int) substr( $vat, -1 ) === $verificador;
}

/**
 * Valida un UID/VAT de Austria (ATU-XXXXXXX con checksum).
 *
 * Acepta y limpia el prefijo `ATU`, verifica longitud y calcula el
 * dígito de control con los ponderadores definidos.
 *
 * @param string $vat VAT austríaco (con o sin ATU).
 * @return bool       true si el checksum coincide; false en caso contrario.
 */
function apg_nif_valida_at( string $vat ): bool {
    $vat = strtoupper( preg_replace( '/[^0-9A-Z]/', '', $vat ) );

    // Permitir variantes comunes de entrada: ATUxxxxxxxx, ATxxxxxxxx, Uxxxxxxxx o solo 8 dígitos
    // 1) Elimina prefijo ATU o AT al principio si existe
    $vat = preg_replace( '/^ATU?/', '', $vat );
    // 2) Si aún queda una U inicial (p. ej. "U12345678"), elimínala
    $vat = preg_replace( '/^U/', '', $vat );

    // Deben quedar EXACTAMENTE 8 dígitos
    if ( strlen( $vat ) !== 8 || ! ctype_digit( $vat ) ) {
        return false;
    }

    $check_digit = (int) substr( $vat, -1 );
    $vat_number = substr( $vat, 0, 7 ); // Changed variable name for clarity.

    $multipliers = array( 1, 2, 1, 2, 1, 2, 1 );
    $sum = 0;

    for ( $i = 0; $i < 7; $i++ ) {
        $product = ( int )$vat_number[ $i ] * $multipliers[ $i ];
        if ( $product > 9 ) {
            $product = intdiv( $product, 10 ) + ( $product % 10 );
        }
        $sum += $product;
    }

    $check = ( 10 - ( ( $sum + 4 ) % 10 ) ) % 10;

    return $check === $check_digit;
}

/**
 * Valida un VAT de Bélgica (10 dígitos; permite 0 inicial).
 *
 * Admite entrada de 9 dígitos anteponiendo 0. Comprueba dígito de
 * control con módulo 97.
 *
 * @param string $vat VAT belga.
 * @return bool       true si pasa el control; false si no.
 */
function apg_nif_valida_be( string $vat ): bool {
    $vat    = preg_replace( '/[^0-9]/', '', $vat );
    if ( strlen( $vat ) === 9 ) {
        $vat    = '0' . $vat;
    }
    if ( strlen( $vat ) !== 10 ) {
        return false;
    }

    $num    = (int)substr( $vat, 0, 8 );
    $check  = (int)substr( $vat, 8, 2 );
    return (97 - ($num % 97)) === $check;
}

/**
 * Valida un VAT de Bulgaria.
 *
 * - 9 dígitos (persona jurídica): doble cálculo de módulo 11.
 * - 10 dígitos (persona física/extranjero): validación por fecha y módulo 11.
 *
 * @param string $vat VAT búlgaro.
 * @return bool       true si es válido; false si no.
 */
function apg_nif_valida_bg( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );

    if ( strlen( $vat ) === 9 ) {
        // Persona jurídica.
        $sum = 0;
        for ( $i = 0; $i < 8; $i++ ) {
            $sum += (int) $vat[ $i ] * ( $i + 1 );
        }
        $check = $sum % 11;
        if ( $check === 10 ) {
            $sum = 0;
            for ( $i = 0; $i < 8; $i++ ) {
                $sum += (int) $vat[ $i ] * ( $i + 3 );
            }
            $check = $sum % 11;
            if ( 10 === $check ) {
                $check = 0;
            }
        }
        return (int) substr( $vat, -1 ) === $check;
    }

    if ( strlen( $vat ) === 10 ) {
        // Persona física o extranjero.
        $month = intval( substr( $vat, 2, 2 ) );
        if ( $month >= 1 && $month <= 12 ) {
            $mult = array(2, 4, 8, 5, 10, 9, 7, 3, 6);
        } elseif ( $month >= 21 && $month <= 32 ) {
            $mult = array(2, 4, 8, 5, 10, 9, 7, 3, 6);
        } elseif ( $month >= 41 && $month <= 52 ) {
            $mult = array(2, 4, 8, 5, 10, 9, 7, 3, 6);
        } else {
            return false;
        }

        $sum = 0;
        for ( $i = 0; $i < 9; $i++ ) {
            $sum += (int) $vat[ $i ] * $mult[ $i ];
        }
        $check = $sum % 11;
        if ( 10 === $check ) {
            $check = 0;
        }

        return (int) substr( $vat, -1 ) === $check;
    }

    return false;
}

/**
 * Valida un UID suizo (CHE123456789) con algoritmo mod 11.
 *
 * @param string $vat VAT suizo.
 * @return bool       true si el dígito de control es correcto; false si no.
 */
function apg_nif_valida_ch( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );
    if ( strlen( $vat ) !== 9 ) {
        return false;
    }

    $mult   = array( 5, 4, 3, 2, 7, 6, 5, 4 );
    $suma   = 0;
    for ( $i = 0; $i < 8; $i++ ) {
        $suma += (int) $vat[ $i ] * $mult[ $i ];
    }

    $resto = $suma % 11;
    $digito = ( 11 - $resto ) % 11;
    if ( 10 === $digito ) {
        return false; // Dígito 10 no permitido.
    }

    return (int) substr( $vat, -1 ) === $digito;
}

/**
 * Valida un RUT chileno (NNNNNNNN-DV).
 *
 * Limpia separadores, separa dígito verificador y calcula DV con
 * ponderadores 2..7 cíclicos (módulo 11, K=10, 0=11).
 *
 * @param string $vat RUT chileno.
 * @return bool       true si DV coincide; false si no.
 *
 * @see https://gist.github.com/punchi/3a5c44e7aa7ac0609ce9e53365572541
 */
function apg_nif_valida_cl( string $vat ): bool {
    // Eliminar puntos y guión.
    $vat    = strtoupper( preg_replace( '/[^0-9K]/', '', $vat ) );
    if ( strlen( $vat ) < 2 ) {
        return false;
    }

    // Separar número y dígito verificador.
    $dv     = substr( $vat, -1 );
    $numero = substr( $vat, 0, -1 );

    // Validar que solo haya números.
    if ( ! ctype_digit( $numero ) ) {
        return false;
    }

    // Cálculo del dígito verificador.
    $suma   = 0;
    $factor = 2;
    for ( $i = strlen( $numero ) - 1; $i >= 0; $i-- ) {
        $suma   += intval( $numero[$i] ) * $factor;
        $factor = $factor == 7 ? 2 : $factor + 1;
    }

    $resto  = $suma % 11;
    $dvr    = 11 - $resto;

    if ( $dvr == 11 ) {
        $dvr    = '0';
    } elseif ( $dvr == 10 ) {
        $dvr    = 'K';
    } else {
        $dvr    = strval( $dvr );
    }

    return $dvr === $dv;
}

/**
 * Valida un VAT de Chipre (8 dígitos + letra, módulo 26).
 *
 * @param string $vat VAT chipriota.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_cy( string $vat ): bool {
    $vat = strtoupper( preg_replace( '/[^0-9A-Z]/', '', $vat ) );
    if ( strlen( $vat ) !== 9 ) {
        return false;
    }

    $numero = substr( $vat, 0, 8 );
    if ( ! ctype_digit( $numero ) || '0' === $numero[0] ) {
        return false;
    }

    $mult = array(1, 2, 1, 2, 1, 2, 1, 2);
    $suma = 0;
    for ( $i = 0; $i < 8; $i++ ) {
        $producto = (int) $numero[ $i ] * $mult[ $i ];
        if ( $producto > 9 ) {
            $producto = intdiv( $producto, 10 ) + ( $producto % 10 );
        }
        $suma += $producto;
    }

    $check = $suma % 26;
    $letra = chr( $check + 65 );

    return substr( $vat, -1 ) === $letra;
}

/**
 * Valida un DIČ/VAT de República Checa.
 *
 * Reglas:
 * - 8 dígitos: checksum con ponderadores decrecientes.
 * - 9 o 10 dígitos: debe ser totalmente numérico.
 *
 * @param string $vat VAT checo.
 * @return bool       true si cumple reglas; false si no.
 */
function apg_nif_valida_cz( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );
    $length = strlen( $vat );

    if ( $length !== 8 && $length !== 9 && $length !== 10 ) {
        return false;
    }

    if ( $length === 8 ) {
        $sum = 0;
        for ( $i = 0; $i < 7; $i++ ) {
            $sum += (int) $vat[ $i ] * ( 8 - $i );
        }
        $check = 11 - ( $sum % 11 );
        if ( 10 === $check ) {
            $check = 0;
        }
        if ( 11 === $check ) {
            $check = 1;
        }
        return (int) substr( $vat, -1 ) === $check;
    }

    if ( $length === 9 || $length === 10 ) {
        return ctype_digit( $vat );
    }

    return false;
}

/**
 * Valida un USt-IdNr. de Alemania (9 dígitos con algoritmo oficial).
 *
 * Aplica el método de producto/suma iterativo y compara el dígito de control.
 *
 * @param string $vat VAT alemán.
 * @return bool       true si el checksum es correcto; false si no.
 */

function apg_nif_valida_de( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', $vat );
	if ( strlen( $vat ) !== 9 ) {
		return false;
	}

	$product = 10;
	for ( $i = 0; $i < 8; $i++ ) {
		// Convertir el carácter a un entero restando el valor ASCII de '0'.
		$digit = (int) $vat[ $i ];
		$sum   = ( $digit + $product ) % 10;
		if ( 0 === $sum ) {
			$sum = 10;
		}
		$product = ( 2 * $sum ) % 11;
	}
	$check = 11 - $product;
	if ( 10 === $check ) {
		$check = 0;
	} elseif ( 11 === $check ) {
		$check = 1;
	}

	return (int) substr( $vat, -1 ) === $check;
}

/**
 * Valida un CVR danés (8 dígitos, módulo 11).
 *
 * @param string $vat VAT danés.
 * @return bool       true si cumple módulo 11; false si no.
 */
function apg_nif_valida_dk( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );
    if ( strlen( $vat ) !== 8 ) {
        return false;
    }

    $mult = array(2, 7, 6, 5, 4, 3, 2, 1);
    $sum = 0;

    for ( $i = 0; $i < 8; $i++ ) {
        $sum += intval( $vat[$i] ) * $mult[$i];
    }

    return ( $sum % 11 ) === 0;
}

/**
 * Valida un KMKR estonio (9 dígitos).
 *
 * Cálculo en dos fases con módulo 11; si el resto es 10 se recalcula.
 *
 * @param string $vat VAT estonio.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_ee( string $vat ): bool {
    // Normaliza: quita separadores, mayúsculas y prefijo EE.
    $vat = strtoupper( $vat );
    $vat = preg_replace( '/[^A-Z0-9]/', '', $vat ?? '' );
    $vat = preg_replace( '/^EE/', '', $vat );

    // Deben quedar exactamente 9 dígitos
    if ( ! preg_match( '/^\d{9}$/', $vat ) ) {
        return false;
    }

    $digits  = array_map( 'intval', str_split( $vat ) );
    $weights = array( 3, 7, 1, 3, 7, 1, 3, 7 );

    $sum = 0;
    for ( $i = 0; $i < 8; $i++ ) {
        $sum += $digits[ $i ] * $weights[ $i ];
    }

    $check = ( 10 - ( $sum % 10 ) ) % 10;
    return $check === $digits[8];
}

/**
 * Valida NIF/CIF/NIE de España.
 *
 * Soporta:
 * - NIF (8 dígitos + letra).
 * - CIF (letra inicial + 7 dígitos + dígito/letra).
 * - NIE (X/Y/Z/T + 7/8 dígitos + letra).
 *
 * @param string $vat Identificador español (con o sin 'ES').
 * @return bool       true si pasa las reglas/dígitos; false si no.
 */
function apg_nif_valida_es( string $vat ): bool {
    $vat_valido = false;
    $vat        = preg_replace( '/[ -,.]/', '', $vat );
    $vat        = str_replace( 'ES', '', $vat );

    $numero = array();
    for ( $i = 0; $i < 9; $i++ ) {
        $numero[ $i ] = substr( $vat, $i, 1 );
    }

    if ( ! preg_match( '/((^[A-Z]{1}[0-9]{7}[A-Z0-9]{1}$|^[T]{1}[A-Z0-9]{8}$)|^[0-9]{8}[A-Z]{1}$)/', $vat ) ) { // No tiene formato válido.
        return false;
    }

    if ( preg_match( '/(^[0-9]{8}[A-Z]{1}$)/', $vat ) ) {
        if ( $numero[8] == substr( 'TRWAGMYFPDXBNJZSQVHLCKE', substr( $vat, 0, 8 ) % 23, 1 ) ) { // NIF válido.
            $vat_valido = true;
        }
    }

    $suma = intval( $numero[2] ) + intval( $numero[4] ) + intval( $numero[6] );
    for ( $i = 1; $i < 8; $i += 2 ) {
        $dig = intval( $numero[ $i ] );
        if ( 2 * $dig >= 10 ) {
            $doble = (string) ( 2 * $dig );
            $suma += intval( substr( $doble, 0, 1 ) ) + intval( substr( $doble, 1, 1 ) );
        } else {
            $suma += 2 * $dig;
        }
    }
    $suma_numero = 10 - ( $suma % 10 );
    if ( 10 === $suma_numero ) {
        $suma_numero = 0;
    }

    if ( preg_match( '/^[KLM]{1}/', $vat ) ) { // NIF especial válido.
        if ( $numero[8] == chr( 64 + $suma_numero ) ) {
            $vat_valido = true;
        }
    }

    if ( preg_match( '/^[ABCDEFGHJNPQRSUVW]{1}/', $vat ) && isset( $numero[8] ) ) {
        if ( $numero[8] == chr( 64 + $suma_numero ) || $numero[8] == substr( (string) $suma_numero, -1, 1 ) ) { // CIF válido.
            $vat_valido = true;
        }
    }

    if ( preg_match( '/^[T]{1}[A-Z0-9]{8}$/', $vat ) ) { // NIE válido (T).
        $vat_valido = true;
    }

    if ( preg_match( '/^[XYZ]{1}/', $vat ) ) { // NIE válido (XYZ).
        if ( $numero[8] == substr( 'TRWAGMYFPDXBNJZSQVHLCKE', substr( str_replace( array( 'X', 'Y', 'Z' ), array( '0', '1', '2' ), $vat ), 0, 8 ) % 23, 1 ) ) {
            $vat_valido = true;
        }
    }

    return $vat_valido;
}

/**
 * Valida un VAT británico (número estándar o branch).
 *
 * @param string $vat VAT del Reino Unido.
 * @return bool       true si el checksum coincide; false si no.
 */
function apg_nif_valida_gb( string $vat ): bool {
    $vat = preg_replace( '/[^0-9A-Z]/', '', strtoupper( $vat ) );

    // Formatos especiales GD/HA.
    if ( preg_match( '/^(GD|HA)\d{3}$/', $vat ) ) {
        return true;
    }

    // Números de 9 o 12 dígitos.
    if ( ! preg_match( '/^\d{9}(\d{3})?$/', $vat ) ) {
        return false;
    }

    $base = substr( $vat, 0, 9 );
    $weights = array(8, 7, 6, 5, 4, 3, 2, 10);
    $sum = 0;
    for ( $i = 0; $i < 8; $i++ ) {
        $sum += (int) $base[ $i ] * $weights[ $i ];
    }

    $check = 97 - ( $sum % 97 );
    if ( $check === 97 ) {
        $check = 0;
    }

    if ( $check !== (int) substr( $base, 7, 2 ) ) {
        return false;
    }

    // Para números > 100000000 se aplica ajuste adicional.
    if ( (int) $base >= 100000000 ) {
        $sum += 55;
        $check = 97 - ( $sum % 97 );
        if ( $check === 97 ) {
            $check = 0;
        }
        return $check === (int) substr( $base, 7, 2 );
    }

    return true;
}

/**
 * Valida un AFM griego (9 dígitos, módulo 11 con potencias de 2).
 *
 * @param string $vat VAT griego.
 * @return bool       true si el dígito final cuadra; false si no.
 */
function apg_nif_valida_gr( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', $vat );
	if ( strlen( $vat ) !== 9 ) {
		return false;
	}
    
	$suma = 0;
	for ( $i = 0; $i < 8; $i++ ) {
		$suma += intval( $vat[ $i ] ) * pow( 2, 8 - $i );
	}
    
	return intval( substr( $vat, -1 ) ) === ( $suma % 11 ) % 10;
}

/**
 * Valida un ALV/Arvonlisävero finlandés (8 dígitos, módulo 11).
 *
 * @param string $vat VAT finlandés.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_fi( string $vat ): bool {
    $vat    = preg_replace( '/[^0-9]/', '', $vat );
    if ( strlen( $vat ) !== 8 ) {
        return false;
    }

    $mult = array( 7, 9, 10, 5, 8, 4, 2 );
    $suma = 0;
    for ( $i = 0; $i < 7; $i++ ) {
        $suma += intval( $vat[ $i ] ) * $mult[ $i ];
    }
    $resto = $suma % 11;
	if ( 0 === $resto ) {
		$control = 0;
	} elseif ( 1 === $resto ) {
		return false;
	} else {
		$control = 11 - $resto;
	}
    
	return $control === intval( substr( $vat, -1 ) );
}

/**
 * Valida un TVA francés.
 *
 * Formatos aceptados:
 * - 11 dígitos (empresas) con clave calculada (12 + 3*(num % 97)) % 97.
 * - Letra + 9 dígitos (personas físicas, sin checksum).
 * - 2 letras + 9 dígitos (entidades especiales, sin checksum).
 *
 * @param string $vat VAT francés (con o sin 'FR').
 * @return bool       true si formato/control son válidos; false si no.
 */
function apg_nif_valida_fr( string $vat ): bool {
    $vat = strtoupper( preg_replace( '/[^A-Z0-9]/', '', $vat ) );
    $vat = str_replace( 'FR', '', $vat );

    // Formato 1: 11 dígitos (empresas).
    if ( ctype_digit( $vat ) && strlen( $vat ) === 11 ) {
        $key = substr( $vat, 0, 2 );
        $number = substr( $vat, 2 );
        $computed_key = ( 12 + 3 * ( $number % 97 ) ) % 97;
        return ( int )$key === $computed_key;
    }

    // Formato 2: Letra + 9 dígitos (personas físicas).
    if ( preg_match( '/^[A-HJ-NP-Z][0-9]{9}$/', $vat ) ) {
        return true; // No hay checksum en este formato.
    }

    // Formato 3: 2 letras + 9 dígitos (entidades especiales).
    if ( preg_match( '/^[A-HJ-NP-Z]{2}[0-9]{9}$/', $vat ) ) {
        return true;
    }

    return false;
}

/**
 * Valida un OIB croata (11 dígitos; algoritmo ISO 7064 mod 11,10).
 *
 * @param string $vat VAT croata.
 * @return bool       true si el dígito verificador coincide; false si no.
 */
function apg_nif_valida_hr( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );
    if ( strlen( $vat ) !== 11 ) {
        return false;
    }

    $product = 10;
    for ( $i = 0; $i < 10; $i++ ) {
        $sum = ( ( int )$vat[ $i ] + $product ) % 10;
        $sum = ( $sum === 0 ) ? 10 : $sum;
        $product = ( 2 * $sum ) % 11;
    }
    $check = ( 11 - $product ) % 10;

    return (int) substr( $vat, -1 ) === $check;
}

/**
 * Valida un adószám húngaro (8 dígitos, pesos {9,7,3,1,9,7,3}).
 *
 * @param string $vat VAT húngaro.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_hu( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );
    $len = strlen( $vat );

    // VIES: 8 dígitos (7 base + dígito de control)
    if ( 8 === $len ) {
        $weights = array( 9, 7, 3, 1, 9, 7, 3 );
        $sum = 0;
        for ( $i = 0; $i < 7; $i++ ) {
            $sum += intval( $vat[ $i ] ) * $weights[ $i ];
        }
        $check = ( 10 - ( $sum % 10 ) ) % 10;
        return intval( substr( $vat, -1 ) ) === $check;
    }

    // Adószám completo: 11 dígitos (8 base + check + 2 sufijos territorio/tipo)
    if ( 11 === $len ) {
        $weights = array( 9, 7, 3, 1, 9, 7, 3, 1 );
        $sum = 0;
        for ( $i = 0; $i < 8; $i++ ) {
            $sum += intval( $vat[ $i ] ) * $weights[ $i ];
        }
        $check = $sum % 10;
        if ( intval( $vat[7] ) !== $check ) {
            return false;
        }
        // No comprobamos los 2 últimos (códigos internos), estructura ya es válida
        return true;
    }

    return false; // 9 dígitos (u otros) no válidos
}

/**
 * Valida VAT de Irlanda.
 *
 * Formatos:
 * - 7 dígitos + letra (A-W) con checksum mod 23.
 * - Patrones antiguos/estendidos (sin checksum).
 *
 * @param string $vat VAT irlandés (con o sin 'IE').
 * @return bool       true si formato/control son válidos; false si no.
 */
function apg_nif_valida_ie( string $vat ): bool {
    $vat = strtoupper( preg_replace( '/[^A-Z0-9]/', '', $vat ) );
    $vat = str_replace( 'IE', '', $vat );

    // Formato 1: 7 dígitos + 1 letra (A-W).
    if ( preg_match( '/^\\d{7}[A-W]$/', $vat ) ) {
        $weights = array( 8, 7, 6, 5, 4, 3, 2 );
        $sum = 0;
        for ( $i = 0; $i < 7; $i++ ) {
            $sum += (int) $vat[ $i ] * $weights[ $i ];
        }
        $check = chr( ( $sum % 23 ) + 64 ); // A=65, B=66, etc.
        return substr( $vat, -1 ) === $check;
    }

    // Formato 2: 1 letra (7-9) + 6 dígitos + 1 letra (A-W).
    if ( preg_match( '/^[7-9][A-Z*+]\\d{5}[A-W]$/', $vat ) ) {
        return true; // No hay checksum en este formato.
    }

    return false;
}

/**
 * Valida un Partita IVA italiano (11 dígitos, algoritmo tipo Luhn).
 *
 * @param string $vat VAT italiano.
 * @return bool       true si el dígito de control coincide; false si no.
 */
function apg_nif_valida_it( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );
	if ( strlen( $vat ) !== 11 ) {
		return false;
	}
    
	$suma = 0;
	for ( $i = 0; $i < 10; $i++ ) {
		$n = (int) $vat[ $i ];
		if ( $i % 2 === 0 ) {
			$suma += $n;
		} else {
			$n *= 2;
			if ( $n > 9 ) {
				$n -= 9;
			}
			$suma += $n;
		}
	}
	$check = ( 10 - ( $suma % 10 ) ) % 10;

    return (int) substr( $vat, -1 ) === $check;
}

/**
 * Valida VAT de Lituania (9 dígitos; doble cálculo mod 11).
 *
 * @param string $vat VAT lituano.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_lt( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );

    if ( strlen( $vat ) === 9 ) {
        $sum = 0;
        for ( $i = 0; $i < 8; $i++ ) {
            $sum += intval( $vat[$i] ) * (1 + $i);
        }

        $check = $sum % 11;
        if ( $check === 10 ) {
            $sum = 0;
            for ( $i = 0; $i < 8; $i++ ) {
                $sum += intval( $vat[$i] ) * (1 + (($i + 2) % 9));
            }
            $check = $sum % 11;
            if ( 10 === $check ) {
                $check = 0;
            }
        }

        return intval( substr( $vat, -1 ) ) === $check;
    }

    if ( strlen( $vat ) === 12 ) {
        // Acepta 12 dígitos (personas jurídicas modernas). Chequeo estructural mínimo.
        return ctype_digit($vat);
    }

    return false;
}

/**
 * Valida VAT de Luxemburgo (8 dígitos; módulo 89).
 *
 * @param string $vat VAT luxemburgués.
 * @return bool       true si (num % 89) coincide; false si no.
 */
function apg_nif_valida_lu( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', $vat );
	if ( strlen( $vat ) !== 8 ) {
		return false;
	}
    
	$num = intval( substr( $vat, 0, 6 ) );
	$check = intval( substr( $vat, -2 ) );
    
	return ( $num % 89 ) === $check;
}

/**
 * Valida VAT de Letonia (11 dígitos).
 *
 * Personas jurídicas: verificación ponderada/módulo.
 * Personas físicas: regla especial (primer dígito > 3).
 *
 * @param string $vat VAT letón.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_lv( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', $vat );
	if ( strlen( $vat ) !== 11 ) {
		return false;
	}
	
	// Validación especial para personas físicas.
	if ( $vat[0] > '3' ) {
		return true;
	}
	
	$suma = 0;
	$mult = array(1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
	for ( $i = 0; $i < 10; $i++ ) {
		$suma += intval( $vat[$i] ) * $mult[$i];
	}
	
	$check = ( $suma % 11 );
	if ( 4 === $check && 1 === intval( substr( $vat, -1 ) ) ) {
		$check = 4;
	}
	
	if ( $check === 10 ) {
		$check = 0;
	}
	
	return intval( substr( $vat, -1 ) ) === $check;
}

/**
 * Valida VAT de Malta (8 dígitos; suma ponderada mod 37).
 *
 * @param string $vat VAT maltés.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_mt( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', $vat );
	if ( strlen( $vat ) !== 8 ) {
		return false;
	}

	$mult = array(3, 4, 6, 7, 8, 9, 10);
	$suma = 0;
	for ( $i = 0; $i < 7; $i++ ) {
		$suma += intval( $vat[$i] ) * $mult[$i];
	}

	$check = $suma % 37;

	return intval( substr( $vat, -1 ) ) === $check;
}

/**
 * Valida VAT de Países Bajos (NNNNNNNNNB0X).
 *
 * Verifica patrón y dígito de control de los 9 primeros dígitos (módulo 11).
 *
 * @param string $vat VAT neerlandés (con sufijo Bxx).
 * @return bool       true si formato/control válidos; false si no.
 */
function apg_nif_valida_nl( string $vat ): bool {
    $vat = strtoupper( preg_replace( '/[^A-Z0-9]/', '', $vat ) );
    if ( ! preg_match( '/^(\d{9})B\d{2}$/', $vat, $matches ) ) {
        return false;
    }

    $base = $matches[1];
    $sum = 0;
    for ( $i = 0; $i < 8; $i++ ) {
        $sum += ( int )$base[ $i ] * ( 9 - $i );
    }

    $check = $sum % 11;
    if ( $check === 10 ) {
        $check = 0;
    }

    return (int) substr( $base, -1 ) === $check;
}

/**
 * Valida MVA noruego (9 dígitos, mod 11; puede llevar sufijo MVA).
 *
 * @param string $vat VAT noruego.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_no( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );
    if ( strlen( $vat ) !== 9 ) {
        return false;
    }

    $weights = array( 3, 2, 7, 6, 5, 4, 3, 2 );
    $sum = 0;
    for ( $i = 0; $i < 8; $i++ ) {
        $sum += (int) $vat[ $i ] * $weights[ $i ];
    }

    $check = 11 - ( $sum % 11 );
    if ( 11 === $check ) {
        $check = 0;
    }
    if ( 10 === $check ) {
        return false; // 10 no es un dígito de control válido.
    }

    return (int) substr( $vat, -1 ) === $check;
}

/**
 * Valida NIP polaco (10 dígitos; pesos y módulo 11).
 *
 * @param string $vat VAT polaco.
 * @return bool       true si el dígito de control coincide; false si no.
 */
function apg_nif_valida_pl( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', $vat );
	if ( strlen( $vat ) !== 10 ) {
		return false;
	}

	$mult = array(6, 5, 7, 2, 3, 4, 5, 6, 7);
	$suma = 0;
	for ( $i = 0; $i < 9; $i++ ) {
		$suma += intval( $vat[$i] ) * $mult[$i];
	}

	$check = $suma % 11;
	if ( $check === 10 ) {
		return false;
	}

	return intval( substr( $vat, -1 ) ) === $check;
}

/**
 * Valida NIF portugués (9 dígitos; primer dígito permitido y módulo 11).
 *
 * @param string $vat NIF/PT VAT.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_pt( string $vat ): bool {
    // Acepta 1, 2, 3, 5, 6, 8, 9 como iniciales válidas.
    if ( ! preg_match( '/^[1235689][0-9]{8}$/', $vat ) ) {
        return false;
    }

    $suma = 0;
    for ( $i = 0; $i < 8; $i++ ) {
        $suma += intval( $vat[ $i ] ) * ( 9 - $i );
    }

    $resto  = $suma % 11;
    $digito = ( $resto < 2 ) ? 0 : 11 - $resto;

    return intval( substr( $vat, -1 ) ) === $digito;
}

/**
 * Valida CUI rumano (2–10 dígitos; ponderación y módulo 11).
 *
 * @param string $vat VAT rumano.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_ro( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );
    $length = strlen( $vat );

    if ( $length < 2 || $length > 10 ) {
        return false;
    }

    $vat = str_pad( $vat, 10, '0', STR_PAD_LEFT );
    $mult = array( 7, 5, 3, 2, 1, 7, 5, 3, 2 );
    $sum = 0;

    for ( $i = 0; $i < 9; $i++ ) {
        $sum += ( int )$vat[ $i ] * $mult[ $i ];
    }

    $check = ( $sum * 10 ) % 11;
    if ( 10 === $check ) {
        $check = 0;
    }

    return (int) substr( $vat, -1 ) === $check;
}

/**
 * Valida un PIB serbio (9 dígitos, módulo 11).
 *
 * @param string $vat VAT serbio.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_rs( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );
    if ( strlen( $vat ) !== 9 ) {
        return false;
    }

    $sum = 0;
    for ( $i = 0; $i < 8; $i++ ) {
        // Pesos 10..3
        $sum += ( 10 - $i ) * (int) $vat[ $i ];
    }

    $check = 11 - ( $sum % 11 );
    if ( $check > 9 ) {
        $check = 0;
    }

    return (int) substr( $vat, -1 ) === $check;
}

/**
 * Valida VAT sueco (12 dígitos, debe terminar en '01'; Luhn sobre los 10 primeros).
 *
 * @param string $vat VAT sueco.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_se( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );
	if ( strlen( $vat ) !== 12 || substr( $vat, -2 ) !== '01' ) {
		return false;
	}
    
	$num = substr( $vat, 0, 10 );
	$suma = 0;
	for ( $i = 0; $i < 10; $i++ ) {
		$tmp = intval( $num[ $i ] ) * ( ( $i % 2 ) ? 1 : 2 );
		if ( $tmp > 9 ) {
			$tmp -= 9;
		}
		$suma += $tmp;
	}
    
    return ( $suma % 10 ) === 0;
}

/**
 * Valida VAT esloveno (8 dígitos; módulo 11 con pesos 8..2).
 *
 * @param string $vat VAT esloveno.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_si( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', $vat );
	if ( strlen( $vat ) !== 8 ) {
		return false;
	}

	$mult = array(8, 7, 6, 5, 4, 3, 2);
	$suma = 0;
	for ( $i = 0; $i < 7; $i++ ) {
		$suma += intval( $vat[$i] ) * $mult[$i];
	}

	$check = 11 - ( $suma % 11 );
	if ( $check === 10 ) {
		return false;
	} elseif ( $check === 11 ) {
		$check = 0;
	}

	return intval( substr( $vat, -1 ) ) === $check;
}

/**
 * Valida VAT eslovaco (10 dígitos; divisible por 11).
 *
 * @param string $vat VAT eslovaco.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_sk( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', $vat );
	if ( strlen( $vat ) !== 10 ) {
		return false;
	}

	return intval( $vat ) % 11 === 0;
}

/**
 * Valida número VAT por estructura (regex) según país.
 *
 * No realiza checksum ni validaciones de contenido; solo comprueba el patrón
 * esperado por país (ISO2). Útil como fallback para países sin algoritmo
 * implementado o como pre-validación.
 *
 * Basado en:
 * - John Gardner (validator JS): http://www.braemoor.co.uk/software/vat.shtml
 * - Contribuciones: https://github.com/mnestorov/regex-patterns
 *
 * @param string $pais       Código ISO2 del país (p. ej., ES, DE, FR).
 * @param string $vat_number Número VAT ya normalizado (sin separadores).
 * @return bool              true si coincide con el patrón; false si no.
 */
function apg_nif_valida_regex( string $pais, string $vat_number ): bool {
    switch ( $pais ) {
        case 'AL': // Albania.
            return ( bool ) preg_match( '/^(AL)?([A-Z]\d{8}[A-Z])$/', $vat_number );
        case 'AD': // Andorra.
            return ( bool ) preg_match( '/^(AD)?([A-Z]\d{6}[A-Z])$/', $vat_number );
        case 'AT': // Austria. 
            return ( bool ) preg_match( '/^(AT)?U(\d{8})$/', $vat_number );
        case 'AX': // Islas de Åland.
            return ( bool ) preg_match( '/^((FI|AX)?\d{8})$/', $vat_number );
        case 'BE': // Bélgica. 
            return ( bool ) preg_match( '/^(BE)?(0?\d{9})$/', $vat_number );
        case 'BG': // Bulgaria.
            return ( bool ) preg_match( '/^(BG)?(\d{9,10})$/', $vat_number );
        case 'BR': // Brasil.
            return ( bool ) preg_match( '/^(BR)?(\d{11}|\d{14})$/', $vat_number );
        case 'BY': // Bielorusia. 
            return ( bool ) preg_match( '/^(BY)?(\d{9})$/', $vat_number );
        case 'CH': // Suiza. 
            return ( bool ) preg_match( '/^(?:CHE)?\d{9}(?:MWST|TVA|IVA)?$/', $vat_number );
        case 'CY': // Chipre. 
            return ( bool ) preg_match( '/^(CY)?([0-5|9]\d{7}[A-Z])$/', $vat_number );
        case 'CZ': // República Checa.
            return ( bool ) preg_match( '/^(CZ)?(\d{8,10})(\d{3})?$/', $vat_number );
        case 'DE': // Alemania. 
            return ( bool ) preg_match( '/^(DE)?([1-9]\d{8})$/', $vat_number );
        case 'DK': // Dinamarca. 
            return ( bool ) preg_match( '/^(DK)?(\d{8})$/', $vat_number );
        case 'EE': // Estonia.
            return ( bool ) preg_match( '/^(EE)?(\d{9})$/', $vat_number );
        case 'ES': // España. 
            return ( bool ) preg_match( '/^(ES)?([A-Z]\d{8})$/', $vat_number ) ||
                preg_match( '/^(ES)?([A-H|N-S|W]\d{7}[A-J])$/', $vat_number ) ||
                preg_match( '/^(ES)?([0-9|Y|Z]\d{7}[A-Z])$/', $vat_number ) ||
                preg_match( '/^(ES)?([K|L|M|X]\d{7}[A-Z])$/', $vat_number );
        case 'EU': // Unión Europea. 
            return ( bool ) preg_match( '/^(EU)?(\d{9})$/', $vat_number );
        case 'FI': // Finlandia. 
            return ( bool ) preg_match( '/^(FI)?(\d{8})$/', $vat_number );
        case 'FO': // Islas Feroe.
            return ( bool ) preg_match( '/^(FO)?(\d{6})$/', $vat_number );
        case 'FR': // Francia. 
            return ( bool ) preg_match( '/^(FR)?(\d{11})$/', $vat_number ) ||
                preg_match( '/^(FR)?([A-HJ-NP-Z]\d{9})$/', $vat_number ) ||
                preg_match( '/^(FR)?([A-HJ-NP-Z]{2}\d{9})$/', $vat_number );
        case 'GB': // Gran Bretaña. 
            return ( bool ) preg_match( '/^(GB)?(\d{9})$/', $vat_number ) ||
                preg_match( '/^(GB)?(\d{12})$/', $vat_number ) ||
                preg_match( '/^(GB)?(GD\d{3})$/', $vat_number ) ||
                preg_match( '/^(GB)?(HA\d{3})$/', $vat_number );
        case 'GR': // Grecia.
            return ( bool ) preg_match( '/^(GR)?(\d{8,9})$/', $vat_number ) ||
                preg_match( '/^(EL)?(\d{9})$/', $vat_number );
        case 'HR': // Croacia. 
            return ( bool ) preg_match( '/^(HR)?(\d{11})$/', $vat_number );
        case 'HU': // Hungría. 
            return ( bool ) preg_match( '/^(HU)?(\d{8})$/', $vat_number );
        case 'IE': // Irlanda. 
            return ( bool ) preg_match( '/^(IE)?(\d{7}[A-W])$/', $vat_number ) ||
                preg_match( '/^(IE)?([7-9][A-Z\*\+]\d{5}[A-W])$/', $vat_number ) ||
                preg_match( '/^(IE)?(\d{7}[A-W][AH])$/', $vat_number );
        case 'IS': // Islandia. 
            return ( bool ) preg_match( '/^(IS)?(\d{5,6})$/', $vat_number );
        case 'IT': // Italia. 
            return ( bool ) preg_match( '/^(IT)?(\d{11})$/', $vat_number );
        case 'LI': // Liechtenstein. 
            return ( bool ) preg_match( '/^(LI)?(\d{5})$/', $vat_number );
        case 'LT': // Lituania. 
            return ( bool ) preg_match( '/^(LT)?(\d{9}|\d{12})$/', $vat_number );
        case 'LU': // Luxemburgo. 
            return ( bool ) preg_match( '/^(LU)?(\d{8})$/', $vat_number );
        case 'LV': // Letonia. 
            return ( bool ) preg_match( '/^(LV)?(\d{11})$/', $vat_number );
        case 'MC': // Mónaco. 
            return ( bool ) preg_match( '/^(FR)?(\d[A-HJ-NP-Z]\d{9})$/', $vat_number ) ||
                preg_match( '/^(FR)?([A-HJ-NP-Z]{2}\d{9})$/', $vat_number );            
        case 'MD': // Moldavia. 
            return ( bool ) preg_match( '/^(MD)?(\d{8})$/', $vat_number );
        case 'ME': // Montenegro. 
            return ( bool ) preg_match( '/^(ME)?(\d{8})$/', $vat_number );
        case 'MK': // Macedonia del Norte. 
            return ( bool ) preg_match( '/^(MK)?(\d{13})$/', $vat_number );
        case 'MT': // Malta.
            return ( bool ) preg_match( '/^(MT)?([1-9]\d{7})$/', $vat_number );
        case 'MX': // México.
            return ( bool ) preg_match( '/^(MX)?([A-Z&]{3,4}\d{6}[A-Z0-9]{3})$/', $vat_number );
        case 'NL': // Países Bajos. 
            return ( bool ) preg_match( '/^(NL)?(\d{9})B\d{2}$/', $vat_number );
        case 'NO': // Noruega. 
            return ( bool ) preg_match( '/^(NO)?(\d{9})(MVA)?$/', $vat_number );
        case 'PL': // Polonia. 
            return ( bool ) preg_match( '/^(PL)?(\d{10})$/', $vat_number );
        case 'PT': // Portugal. 
            return ( bool ) preg_match( '/^(PT)?(\d{9})$/', $vat_number );
        case 'RO': // Rumanía. 
            return ( bool ) preg_match( '/^(RO)?([1-9]\d{1,9})$/', $vat_number );
        case 'RS': // Serbia. 
            return ( bool ) preg_match( '/^(RS)?(\d{9})$/', $vat_number );
        case 'SE': // Suecia. 
            return ( bool ) preg_match( '/^(SE)?(\d{10}01)$/', $vat_number );
        case 'SI': // Eslovenia. 
            return ( bool ) preg_match( '/^(SI)?([1-9]\d{7,8})$/', $vat_number );
        case 'SK': // República Eslovaca.
            return ( bool ) preg_match( '/^(SK)?([1-9]\d{9})$/', $vat_number );
        case 'SM': // San Marino.
            return ( bool ) preg_match( '/^(SM)?(\d{5})$/', $vat_number );
        case 'UA': // Ucrania.
            return ( bool ) preg_match( '/^(UA)?(\d{12})$/', $vat_number );
        default:
            return false;
    }
}
