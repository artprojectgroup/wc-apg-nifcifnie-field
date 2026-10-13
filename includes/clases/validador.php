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
 * Calcula el resto del ISO 7064 MOD 11,10 (Serbia, Montenegro y afines).
 *
 * @param string $digitos Dígitos, incluido el de control.
 * @return int            Resto: 1 si el número es correcto.
 */
function apg_nif_mod_11_10( string $digitos ): int {
    $control = 5;
    for ( $i = 0, $total = strlen( $digitos ); $i < $total; $i++ ) {
        $control = ( ( ( 0 === $control ? 10 : $control ) * 2 ) % 11 + (int) $digitos[ $i ] ) % 10;
    }

    return $control;
}

/**
 * Calcula el resto módulo 97 de una cadena alfanumérica (A=10...Z=35, +=36, *=37).
 *
 * Se trocea para no desbordar el entero, como en el IBAN.
 *
 * @param string $cadena Cadena a convertir.
 * @return int           Resto de dividir entre 97.
 */
function apg_nif_mod_97( string $cadena ): int {
    $numerico = '';
    for ( $i = 0, $total = strlen( $cadena ); $i < $total; $i++ ) {
        $caracter = $cadena[ $i ];
        if ( ctype_digit( $caracter ) ) {
            $numerico .= $caracter;
        } elseif ( '+' === $caracter ) {
            $numerico .= '36';
        } elseif ( '*' === $caracter ) {
            $numerico .= '37';
        } else {
            $numerico .= (string) ( ord( $caracter ) - 55 );
        }
    }

    $resto = 0;
    foreach ( str_split( $numerico, 7 ) as $tramo ) {
        $resto = (int) ( ( $resto . $tramo ) % 97 );
    }

    return $resto;
}

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
 * Valida VAT chipriota (ocho dígitos y letra de control).
 *
 * Los dígitos de las posiciones pares se traducen por una tabla oficial antes de sumar;
 * el resto módulo 26 da la letra de control.
 *
 * @param string $vat VAT chipriota.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_cy( string $vat ): bool {
    $vat = strtoupper( preg_replace( '/[^0-9A-Z]/', '', $vat ) );
    $vat = preg_replace( '/^CY/', '', $vat );
    if ( strlen( $vat ) !== 9 ) {
        return false;
    }

    $numero = substr( $vat, 0, 8 );
    if ( ! ctype_digit( $numero ) ) {
        return false;
    }

    // Los números que empiezan por 12 no se asignan.
    if ( '12' === substr( $numero, 0, 2 ) ) {
        return false;
    }

    $traduccion = array( 1, 0, 5, 7, 9, 13, 15, 17, 19, 21 );
    $suma       = 0;
    for ( $i = 0; $i < 8; $i++ ) {
        $digito = (int) $numero[ $i ];
        $suma  += ( 0 === $i % 2 ) ? $traduccion[ $digito ] : $digito;
    }

    return substr( $vat, -1 ) === chr( ( $suma % 26 ) + 65 );
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

    if ( preg_match( '/^[KLM]{1}/', $vat ) ) { // NIF de persona física (K, L, M).
        // La letra sale de los siete dígitos, con la tabla del DNI.
        if ( $numero[8] === substr( 'TRWAGMYFPDXBNJZSQVHLCKE', intval( substr( $vat, 1, 7 ) ) % 23, 1 ) ) {
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
    $vat = preg_replace( '/^GB/', '', $vat );

    // Organismos públicos: GD000-GD499 (departamentos) y HA500-HA999 (autoridades sanitarias).
    if ( preg_match( '/^(GD|HA)\d{3}$/', $vat ) ) {
        return true;
    }

    // Nueve dígitos, o doce cuando el número identifica una sucursal.
    if ( ! preg_match( '/^\d{9}(\d{3})?$/', $vat ) ) {
        return false;
    }

    $base = substr( $vat, 0, 9 );

    // El bloque de siete dígitos no puede ser cero.
    if ( 0 === (int) substr( $base, 0, 7 ) ) {
        return false;
    }

    // Suma ponderada de los siete primeros dígitos.
    $pesos = array( 8, 7, 6, 5, 4, 3, 2 );
    $total = 0;
    for ( $i = 0; $i < 7; $i++ ) {
        $total += (int) $base[ $i ] * $pesos[ $i ];
    }

    $control = (int) substr( $base, 7, 2 );

    // HMRC usa dos algoritmos y el número es válido si cumple cualquiera de los dos: el
    // clásico (módulo 97) y el posterior a 2001, que añade 55 antes de la comprobación.
    return ( 0 === ( $total + $control ) % 97 ) || ( 0 === ( $total + $control + 55 ) % 97 );
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
 * Valida TVA francés (once caracteres: clave de control y SIREN).
 *
 * La clave numérica cumple el módulo 97 clásico; los números asignados desde 2001 llevan
 * clave alfanumérica, sin la I ni la O, con su propia comprobación módulo 11.
 *
 * @param string $vat TVA francés.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_fr( string $vat ): bool {
    $vat = strtoupper( preg_replace( '/[^A-Z0-9]/', '', $vat ) );
    $vat = preg_replace( '/^FR/', '', $vat );

    if ( 11 !== strlen( $vat ) ) {
        return false;
    }

    $alfabeto = '0123456789ABCDEFGHJKLMNPQRSTUVWXYZ';
    $primero  = strpos( $alfabeto, $vat[0] );
    $segundo  = strpos( $alfabeto, $vat[1] );
    $siren    = substr( $vat, 2 );
    if ( false === $primero || false === $segundo || ! ctype_digit( $siren ) ) {
        return false;
    }

    // Clave numérica: los dos primeros dígitos son (SIREN * 100 + 12) módulo 97.
    if ( ctype_digit( substr( $vat, 0, 2 ) ) ) {
        return (int) substr( $vat, 0, 2 ) === ( 12 + 3 * ( (int) $siren % 97 ) ) % 97;
    }

    // Clave alfanumérica.
    $clave = ctype_digit( $vat[0] ) ? ( $primero * 24 + $segundo - 10 ) : ( $primero * 34 + $segundo - 100 );

    return ( ( (int) $siren + 1 + intdiv( $clave, 11 ) ) % 11 ) === ( $clave % 11 );
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
 * Valida VAT irlandés (ocho o nueve caracteres con letra de control módulo 23).
 *
 * Hay dos formas: la moderna, con siete dígitos y una o dos letras al final, y la antigua,
 * con una letra o los signos + y * en segunda posición.
 *
 * @param string $vat VAT irlandés.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_ie( string $vat ): bool {
    $vat = strtoupper( preg_replace( '/[^A-Z0-9+*]/', '', $vat ) );
    $vat = preg_replace( '/^IE/', '', $vat );

    $largo = strlen( $vat );
    if ( 8 !== $largo && 9 !== $largo ) {
        return false;
    }

    // Primer carácter y posiciones tercera a séptima, dígitos.
    if ( ! ctype_digit( $vat[0] ) || ! ctype_digit( substr( $vat, 2, 5 ) ) ) {
        return false;
    }

    // Las letras finales deben pertenecer al alfabeto de control.
    $alfabeto = 'WABCDEFGHIJKLMNOPQRSTUV';
    for ( $i = 7; $i < $largo; $i++ ) {
        if ( false === strpos( $alfabeto, $vat[ $i ] ) ) {
            return false;
        }
    }

    if ( ctype_digit( substr( $vat, 0, 7 ) ) ) {
        // Forma moderna: la segunda letra, si está, entra en el cálculo.
        $base  = substr( $vat, 0, 7 );
        $extra = substr( $vat, 8 );
    } elseif ( preg_match( '/^[A-Z+*]$/', $vat[1] ) ) {
        // Forma antigua: el primer dígito pasa al final y la segunda posición se ignora.
        $base  = substr( $vat, 2, 5 ) . $vat[0];
        $extra = '';
    } else {
        return false;
    }

    $base = str_pad( $base, 7, '0', STR_PAD_LEFT );
    $suma = 0;
    for ( $i = 0; $i < 7; $i++ ) {
        $suma += ( 8 - $i ) * (int) $base[ $i ];
    }
    if ( '' !== $extra ) {
        $suma += 9 * strpos( $alfabeto, $extra );
    }

    return $vat[7] === $alfabeto[ $suma % 23 ];
}

/**
 * Valida un Codice Fiscale italiano (16 caracteres) con letra de control.
 *
 * @param string $vat Identificador italiano sin separadores.
 * @return bool       true si el formato básico y la letra de control coinciden.
 */
function apg_nif_valida_it_codice_fiscale( string $vat ): bool {
    $vat = strtoupper( preg_replace( '/[^A-Z0-9]/', '', $vat ) );
    if ( ! preg_match( '/^[A-Z]{6}[0-9LMNPQRSTUV]{2}[ABCDEHLMPRST][0-9LMNPQRSTUV]{2}[A-Z][0-9LMNPQRSTUV]{3}[A-Z]$/', $vat ) ) {
        return false;
    }

    $impares = array(
        '0' => 1,  '1' => 0,  '2' => 5,  '3' => 7,  '4' => 9,  '5' => 13, '6' => 15, '7' => 17, '8' => 19, '9' => 21,
        'A' => 1,  'B' => 0,  'C' => 5,  'D' => 7,  'E' => 9,  'F' => 13, 'G' => 15, 'H' => 17, 'I' => 19, 'J' => 21,
        'K' => 2,  'L' => 4,  'M' => 18, 'N' => 20, 'O' => 11, 'P' => 3,  'Q' => 6,  'R' => 8,  'S' => 12, 'T' => 14,
        'U' => 16, 'V' => 10, 'W' => 22, 'X' => 25, 'Y' => 24, 'Z' => 23,
    );
    $pares = array(
        '0' => 0,  '1' => 1,  '2' => 2,  '3' => 3,  '4' => 4,  '5' => 5,  '6' => 6,  '7' => 7,  '8' => 8,  '9' => 9,
        'A' => 0,  'B' => 1,  'C' => 2,  'D' => 3,  'E' => 4,  'F' => 5,  'G' => 6,  'H' => 7,  'I' => 8,  'J' => 9,
        'K' => 10, 'L' => 11, 'M' => 12, 'N' => 13, 'O' => 14, 'P' => 15, 'Q' => 16, 'R' => 17, 'S' => 18, 'T' => 19,
        'U' => 20, 'V' => 21, 'W' => 22, 'X' => 23, 'Y' => 24, 'Z' => 25,
    );

    $suma = 0;
    for ( $i = 0; $i < 15; $i++ ) {
        $caracter = $vat[ $i ];
        $suma += ( 0 === $i % 2 ) ? $impares[ $caracter ] : $pares[ $caracter ];
    }

    $control = chr( 65 + ( $suma % 26 ) );
    return substr( $vat, -1 ) === $control;
}

/**
 * Valida un identificador italiano:
 * - Partita IVA (11 dígitos, algoritmo tipo Luhn).
 * - Codice Fiscale (16 caracteres con letra de control).
 *
 * @param string $vat Identificador italiano.
 * @return bool       true si coincide alguno de los formatos soportados.
 */
function apg_nif_valida_it( string $vat ): bool {
    $vat_alnum = strtoupper( preg_replace( '/[^A-Z0-9]/', '', $vat ) );
    $vat_alnum = preg_replace( '/^IT/', '', $vat_alnum );
    if ( strlen( $vat_alnum ) === 16 ) {
        return apg_nif_valida_it_codice_fiscale( $vat_alnum );
    }

    $vat = preg_replace( '/[^0-9]/', '', $vat_alnum );
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
 * Valida PVM lituano (nueve o doce dígitos, módulo 11 con dos juegos de ponderadores).
 *
 * @param string $vat PVM.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_lt( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );
    $largo = strlen( $vat );

    if ( 9 !== $largo && 12 !== $largo ) {
        return false;
    }

    // En los números de doce dígitos la posición once siempre es un 1.
    if ( 12 === $largo && '1' !== $vat[10] ) {
        return false;
    }

    $digitos = $largo - 1;
    $suma    = 0;
    for ( $i = 0; $i < $digitos; $i++ ) {
        $suma += ( 1 + $i % 9 ) * intval( $vat[ $i ] );
    }

    $check = $suma % 11;
    if ( 10 === $check ) {
        $suma = 0;
        for ( $i = 0; $i < $digitos; $i++ ) {
            $suma += ( 1 + ( $i + 2 ) % 9 ) * intval( $vat[ $i ] );
        }
        $check = $suma % 11 % 10;
    }

    return intval( substr( $vat, -1 ) ) === $check;
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
 * Valida PVN letón (once dígitos).
 *
 * Las personas jurídicas empiezan por un dígito mayor que 3 y se comprueban con una suma
 * ponderada; las físicas usan su código personal, con fecha de nacimiento y dígito de control.
 *
 * @param string $vat PVN.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_lv( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', $vat );
	if ( strlen( $vat ) !== 11 ) {
		return false;
	}

	// Personas jurídicas.
	if ( $vat[0] > '3' ) {
		$mult = array( 9, 1, 4, 8, 3, 10, 2, 5, 7, 6, 1 );
		$suma = 0;
		for ( $i = 0; $i < 11; $i++ ) {
			$suma += $mult[ $i ] * intval( $vat[ $i ] );
		}

		return 3 === $suma % 11;
	}

	// Personas físicas: los códigos que empiezan por 32 no llevan fecha.
	if ( '32' !== substr( $vat, 0, 2 ) ) {
		$dia  = (int) substr( $vat, 0, 2 );
		$mes  = (int) substr( $vat, 2, 2 );
		$anio = 1800 + ( (int) $vat[6] ) * 100 + (int) substr( $vat, 4, 2 );
		if ( ! checkdate( $mes, $dia, $anio ) ) {
			return false;
		}
	}

	$mult = array( 10, 5, 8, 4, 2, 1, 6, 3, 7, 9 );
	$suma = 1;
	for ( $i = 0; $i < 10; $i++ ) {
		$suma += $mult[ $i ] * intval( $vat[ $i ] );
	}

	return intval( $vat[10] ) === $suma % 11 % 10;
}

/**
 * Valida VAT maltés (ocho dígitos, módulo 37).
 *
 * @param string $vat VAT maltés.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_mt( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', preg_replace( '/^MT/', '', strtoupper( $vat ) ) );
	if ( strlen( $vat ) !== 8 || '0' === $vat[0] ) {
		return false;
	}

	// La suma ponderada de los ocho dígitos, el último incluido, es múltiplo de 37.
	$mult = array( 3, 4, 6, 7, 8, 9, 10, 1 );
	$suma = 0;
	for ( $i = 0; $i < 8; $i++ ) {
		$suma += intval( $vat[ $i ] ) * $mult[ $i ];
	}

	return 0 === $suma % 37;
}

/**
 * Valida VAT de Países Bajos (nueve caracteres, la letra B y el subnúmero).
 *
 * Hay dos juegos de números en circulación y basta con que el número cumpla uno:
 * los antiguos derivan del número de identificación personal y cumplen el módulo 11
 * («elfproef»); los asignados desde 2020 se generan sin esa relación, pueden llevar letras
 * y los signos + y *, y se comprueban con un módulo 97 sobre el número con el prefijo NL.
 *
 * @param string $vat VAT neerlandés (con sufijo Bxx).
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_nl( string $vat ): bool {
    $vat = strtoupper( preg_replace( '/[^A-Z0-9+*]/', '', $vat ) );
    $vat = preg_replace( '/^NL/', '', $vat );

    if ( ! preg_match( '/^[A-Z0-9+*]{9}B\d{2}$/', $vat ) ) {
        return false;
    }

    // El subnúmero que sigue a la B empieza en 01.
    if ( '00' === substr( $vat, -2 ) ) {
        return false;
    }

    $bloque = substr( $vat, 0, 9 );

    // Números antiguos: módulo 11 sobre los nueve dígitos.
    if ( ctype_digit( $bloque ) ) {
        if ( '000000000' === $bloque ) {
            return false;
        }

        $suma = 0;
        for ( $i = 0; $i < 8; $i++ ) {
            $suma += ( 9 - $i ) * (int) $bloque[ $i ];
        }

        $resto = $suma % 11;
        if ( 10 !== $resto && (int) $bloque[8] === $resto ) {
            return true;
        }
    }

    // btw-id asignado desde 2020: módulo 97, como el IBAN.
    return 1 === apg_nif_mod_97( 'NL' . $vat );
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
 * Valida NIF portugués (nueve dígitos, módulo 11).
 *
 * @param string $vat NIF portugués.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_pt( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', preg_replace( '/^PT/', '', strtoupper( $vat ) ) );

    // Cualquier inicial de 1 a 9: las series en uso incluyen la 4 y la 7.
    if ( ! preg_match( '/^[1-9][0-9]{8}$/', $vat ) ) {
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
 * Valida CF rumano: CUI de empresa (2-10 dígitos) o CNP de particular (13 dígitos).
 *
 * @param string $vat CF rumano.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_ro( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );
    $length = strlen( $vat );

    // CNP de persona física.
    if ( 13 === $length ) {
        if ( '0' === $vat[0] ) {
            return false;
        }

        $mes = (int) substr( $vat, 3, 2 );
        $dia = (int) substr( $vat, 5, 2 );
        if ( $mes < 1 || $mes > 12 || $dia < 1 || $dia > 31 ) {
            return false;
        }

        $mult = array( 2, 7, 9, 1, 4, 6, 3, 5, 8, 2, 7, 9 );
        $sum  = 0;
        for ( $i = 0; $i < 12; $i++ ) {
            $sum += (int) $vat[ $i ] * $mult[ $i ];
        }

        $check = $sum % 11;
        if ( 10 === $check ) {
            $check = 1;
        }

        return (int) $vat[12] === $check;
    }

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
 * Valida PIB serbio (nueve dígitos, ISO 7064 MOD 11,10).
 *
 * @param string $vat PIB.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_rs( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', preg_replace( '/^RS/', '', strtoupper( $vat ) ) );
    if ( strlen( $vat ) !== 9 ) {
        return false;
    }

    return 1 === apg_nif_mod_11_10( $vat );
}

/**
 * Valida VAT sueco (doce dígitos: número de organización y sufijo de registro).
 *
 * @param string $vat VAT sueco.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_se( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', $vat );
	if ( strlen( $vat ) !== 12 ) {
		return false;
	}

	// El sufijo numera cada registro de IVA del titular: del 01 al 94.
	$sufijo = (int) substr( $vat, -2 );
	if ( $sufijo < 1 || $sufijo > 94 ) {
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
 * Valida VAT eslovaco (ocho dígitos, módulo 11).
 *
 * @param string $vat VAT eslovaco.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_si( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', preg_replace( '/^SI/', '', strtoupper( $vat ) ) );
	if ( strlen( $vat ) !== 8 || '0' === $vat[0] ) {
		return false;
	}

	$mult = array(8, 7, 6, 5, 4, 3, 2);
	$suma = 0;
	for ( $i = 0; $i < 7; $i++ ) {
		$suma += intval( $vat[$i] ) * $mult[$i];
	}

	// Resto 1 da control 10, que se escribe como 0; resto 0 daría 11, que no se asigna.
	$check = 11 - ( $suma % 11 );
	if ( 10 === $check ) {
		$check = 0;
	} elseif ( 11 === $check ) {
		return false;
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
 * Valida un identificador fiscal de Islandia.
 *
 * Acepta dos formatos:
 * - VSK (número de IVA): 5 o 6 dígitos, sin dígito de control.
 * - Kennitala (número de identidad): 10 dígitos, con dígito de control en la novena
 *   posición (módulo 11 con los ponderadores 3, 2, 7, 6, 5, 4, 3, 2).
 *
 * @param string $vat Identificador islandés.
 * @return bool       true si es válido; false en caso contrario.
 */
function apg_nif_valida_is( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', preg_replace( '/^IS/', '', strtoupper( $vat ) ) );

	// Número de IVA: sin dígito de control que comprobar.
	if ( preg_match( '/^\d{5,6}$/', $vat ) ) {
		return true;
	}

	if ( ! preg_match( '/^\d{10}$/', $vat ) ) {
		return false;
	}

	$ponderadores = array( 3, 2, 7, 6, 5, 4, 3, 2 );
	$suma         = 0;
	for ( $i = 0; $i < 8; $i++ ) {
		$suma += (int) $vat[ $i ] * $ponderadores[ $i ];
	}

	$resto   = $suma % 11;
	$control = ( 0 === $resto ) ? 0 : 11 - $resto;

	// El 10 no puede representarse en una sola posición.
	if ( 10 === $control ) {
		return false;
	}

	return $control === (int) $vat[8];
}

/**
 * Valida CNPJ (empresa) o CPF (particular) brasileños, módulo 11.
 *
 * El CNPJ admite letras desde 2026: cada carácter vale su código ASCII menos 48.
 *
 * @param string $vat CNPJ o CPF.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_br( string $vat ): bool {
	$vat = preg_replace( '/[^0-9A-Z]/', '', preg_replace( '/^BR/', '', strtoupper( $vat ) ) );

	if ( preg_match( '/^(.)\1+$/', $vat ) ) {
		return false;
	}

	/**
	 * Calcula un dígito verificador por módulo 11.
	 *
	 * @param string            $base          Caracteres sobre los que se calcula.
	 * @param array<int,int>    $ponderadores  Ponderadores en el mismo orden.
	 * @return int                             Dígito verificador.
	 */
	$digito = function ( string $base, array $ponderadores ): int {
		$suma = 0;
		for ( $i = 0, $total = strlen( $base ); $i < $total; $i++ ) {
			$suma += ( ord( $base[ $i ] ) - 48 ) * $ponderadores[ $i ];
		}
		$resto = $suma % 11;

		return ( $resto < 2 ) ? 0 : 11 - $resto;
	};

	// CNPJ: doce caracteres alfanuméricos y dos dígitos de control.
	if ( preg_match( '/^[0-9A-Z]{12}\d{2}$/', $vat ) ) {
		$primero  = $digito( substr( $vat, 0, 12 ), array( 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2 ) );
		$segundo  = $digito( substr( $vat, 0, 13 ), array( 6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2 ) );

		return $primero === (int) $vat[12] && $segundo === (int) $vat[13];
	}

	// CPF: once dígitos.
	if ( preg_match( '/^\d{11}$/', $vat ) ) {
		$primero = $digito( substr( $vat, 0, 9 ), array( 10, 9, 8, 7, 6, 5, 4, 3, 2 ) );
		$segundo = $digito( substr( $vat, 0, 10 ), array( 11, 10, 9, 8, 7, 6, 5, 4, 3, 2 ) );

		return $primero === (int) $vat[9] && $segundo === (int) $vat[10];
	}

	return false;
}

/**
 * Valida un RUC de Perú (11 dígitos, módulo 11).
 *
 * @param string $vat RUC peruano.
 * @return bool       true si el dígito verificador cuadra.
 */
function apg_nif_valida_pe( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', preg_replace( '/^PE/', '', strtoupper( $vat ) ) );

	if ( ! preg_match( '/^\d{11}$/', $vat ) ) {
		return false;
	}

	$ponderadores = array( 5, 4, 3, 2, 7, 6, 5, 4, 3, 2 );
	$suma         = 0;
	for ( $i = 0; $i < 10; $i++ ) {
		$suma += (int) $vat[ $i ] * $ponderadores[ $i ];
	}

	$control = 11 - ( $suma % 11 );
	if ( $control >= 10 ) {
		$control -= 10;
	}

	return $control === (int) $vat[10];
}

/**
 * Valida un NIT de Colombia (módulo 11 con ponderadores primos).
 *
 * @param string $vat NIT con su dígito de verificación al final.
 * @return bool       true si el dígito de verificación cuadra.
 */
function apg_nif_valida_co( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', preg_replace( '/^CO/', '', strtoupper( $vat ) ) );

	if ( ! preg_match( '/^\d{9,16}$/', $vat ) ) {
		return false;
	}

	$primos   = array( 3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71 );
	$base     = strrev( substr( $vat, 0, -1 ) );
	$esperado = (int) substr( $vat, -1 );
	$suma     = 0;

	for ( $i = 0, $total = strlen( $base ); $i < $total; $i++ ) {
		$suma += (int) $base[ $i ] * $primos[ $i ];
	}

	$resto = $suma % 11;

	return ( ( $resto < 2 ) ? $resto : 11 - $resto ) === $esperado;
}

/**
 * Valida un RUT de Uruguay (12 dígitos, módulo 11).
 *
 * @param string $vat RUT uruguayo.
 * @return bool       true si el dígito verificador cuadra.
 */
function apg_nif_valida_uy( string $vat ): bool {
	$vat = preg_replace( '/[^0-9]/', '', preg_replace( '/^UY/', '', strtoupper( $vat ) ) );

	if ( ! preg_match( '/^\d{12}$/', $vat ) ) {
		return false;
	}

	$ponderadores = array( 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2 );
	$suma         = 0;
	for ( $i = 0; $i < 11; $i++ ) {
		$suma += (int) $vat[ $i ] * $ponderadores[ $i ];
	}

	$resto   = $suma % 11;
	$control = ( 0 === $resto ) ? 0 : 11 - $resto;

	if ( 10 === $control ) {
		return false;
	}

	return $control === (int) $vat[11];
}

/**
 * Valida la estructura de un RFC de México.
 *
 * Comprueba el patrón (3 letras para empresas o 4 para personas físicas, la fecha de
 * constitución o nacimiento y la homoclave) y que la fecha sea real. No se comprueba el
 * dígito verificador: su cálculo depende de tablas de conversión con casos especiales y
 * un error ahí rechazaría RFC válidos, que es peor que no comprobarlo.
 *
 * @param string $vat RFC mexicano.
 * @return bool       true si la estructura y la fecha son correctas.
 */
function apg_nif_valida_mx( string $vat ): bool {
	$vat = strtoupper( preg_replace( '/[^A-Z0-9&Ñ]/i', '', $vat ) );

	if ( ! preg_match( '/^([A-ZÑ&]{3,4})(\d{6})([A-Z0-9]{3})$/', $vat, $partes ) ) {
		return false;
	}

	$fecha = $partes[2];
	$anio  = (int) substr( $fecha, 0, 2 );
	$mes   = (int) substr( $fecha, 2, 2 );
	$dia   = (int) substr( $fecha, 4, 2 );

	if ( $mes < 1 || $mes > 12 ) {
		return false;
	}

	// Se prueban los dos siglos posibles, porque el año va con dos cifras.
	return checkdate( $mes, $dia, 1900 + $anio ) || checkdate( $mes, $dia, 2000 + $anio );
}

/**
 * Valida NIPT albanés (letra A-M, ocho dígitos y letra).
 *
 * No tiene dígito de control.
 *
 * @param string $vat NIPT.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_al( string $vat ): bool {
    $vat = strtoupper( preg_replace( '/[^A-Z0-9]/', '', $vat ) );
    $vat = preg_replace( '/^AL/', '', $vat );

    return (bool) preg_match( '/^[A-M]\d{8}[A-Z]$/', $vat );
}

/**
 * Valida NRT andorrano (letra, seis dígitos y letra).
 *
 * La letra inicial indica el tipo de obligado tributario; no hay dígito de control.
 *
 * @param string $vat NRT.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_ad( string $vat ): bool {
    $vat = strtoupper( preg_replace( '/[^A-Z0-9]/', '', $vat ) );
    $vat = preg_replace( '/^AD/', '', $vat );

    return (bool) preg_match( '/^[ACDEFGLOPU]\d{6}[A-Z]$/', $vat );
}

/**
 * Valida UNP bielorruso (nueve caracteres con dígito de control módulo 11).
 *
 * Los dos primeros caracteres pueden ser letras de la región; el resto, dígitos.
 *
 * @param string $vat UNP.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_by( string $vat ): bool {
    $vat = strtoupper( preg_replace( '/[^A-Z0-9]/', '', $vat ) );
    $vat = preg_replace( '/^(BY|UNP)/', '', $vat );

    if ( 9 !== strlen( $vat ) || ! ctype_digit( substr( $vat, 2 ) ) ) {
        return false;
    }

    $regiones = 'ABCEHKMOPT';
    if ( false === strpos( '1234567ABCEHKM', $vat[0] ) ) {
        return false;
    }

    // El segundo carácter, si es letra, se convierte en su posición dentro de las regiones.
    $base = $vat;
    if ( ! ctype_digit( substr( $vat, 0, 2 ) ) ) {
        $posicion = strpos( $regiones, $vat[1] );
        if ( false === $posicion ) {
            return false;
        }
        $base = $vat[0] . $posicion . substr( $vat, 2 );
    }

    $alfabeto = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $mult     = array( 29, 23, 19, 17, 13, 7, 5, 3 );
    $suma     = 0;
    for ( $i = 0; $i < 8; $i++ ) {
        $valor = strpos( $alfabeto, $base[ $i ] );
        if ( false === $valor ) {
            return false;
        }
        $suma += $mult[ $i ] * $valor;
    }

    $control = $suma % 11;

    return $control < 10 && (int) $vat[8] === $control;
}

/**
 * Valida IDNO moldavo (trece dígitos con dígito de control).
 *
 * @param string $vat IDNO.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_md( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', preg_replace( '/^MD/', '', strtoupper( $vat ) ) );
    if ( 13 !== strlen( $vat ) ) {
        return false;
    }

    $mult = array( 7, 3, 1, 7, 3, 1, 7, 3, 1, 7, 3, 1 );
    $suma = 0;
    for ( $i = 0; $i < 12; $i++ ) {
        $suma += $mult[ $i ] * (int) $vat[ $i ];
    }

    return (int) $vat[12] === $suma % 10;
}

/**
 * Valida PIB montenegrino (ocho dígitos, módulo 11).
 *
 * @param string $vat PIB.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_me( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', preg_replace( '/^ME/', '', strtoupper( $vat ) ) );
    if ( 8 !== strlen( $vat ) ) {
        return false;
    }

    $mult = array( 8, 7, 6, 5, 4, 3, 2 );
    $suma = 0;
    for ( $i = 0; $i < 7; $i++ ) {
        $suma += $mult[ $i ] * (int) $vat[ $i ];
    }

    $control = ( ( ( -$suma % 11 ) + 11 ) % 11 ) % 10;

    return (int) $vat[7] === $control;
}

/**
 * Valida EDB macedonio (trece dígitos, módulo 11).
 *
 * @param string $vat EDB.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_mk( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', preg_replace( '/^MK/', '', strtoupper( $vat ) ) );
    if ( 13 !== strlen( $vat ) ) {
        return false;
    }

    $mult = array( 7, 6, 5, 4, 3, 2, 7, 6, 5, 4, 3, 2 );
    $suma = 0;
    for ( $i = 0; $i < 12; $i++ ) {
        $suma += $mult[ $i ] * (int) $vat[ $i ];
    }

    $control = ( ( ( -$suma % 11 ) + 11 ) % 11 ) % 10;

    return (int) $vat[12] === $control;
}

/**
 * Valida EDRPOU ucraniano (ocho dígitos, módulo 11 con dos juegos de ponderadores).
 *
 * @param string $vat EDRPOU.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_ua( string $vat ): bool {
    $vat = preg_replace( '/[^0-9]/', '', preg_replace( '/^UA/', '', strtoupper( $vat ) ) );
    if ( 8 !== strlen( $vat ) ) {
        return false;
    }

    $mult = ( false !== strpos( '345', $vat[0] ) ) ? array( 7, 1, 2, 3, 4, 5, 6 ) : array( 1, 2, 3, 4, 5, 6, 7 );
    $suma = 0;
    for ( $i = 0; $i < 7; $i++ ) {
        $suma += $mult[ $i ] * (int) $vat[ $i ];
    }

    $control = $suma % 11;
    if ( $control > 9 ) {
        // Segunda pasada con los ponderadores aumentados en dos.
        $suma = 0;
        for ( $i = 0; $i < 7; $i++ ) {
            $suma += ( $mult[ $i ] + 2 ) * (int) $vat[ $i ];
        }
        $control = $suma % 11 % 10;
    }

    return (int) $vat[7] === $control;
}

/**
 * Valida COE sanmarinense (hasta cinco dígitos, sin dígito de control).
 *
 * @param string $vat COE.
 * @return bool       true si válido; false si no.
 */
function apg_nif_valida_sm( string $vat ): bool {
    $vat = strtoupper( preg_replace( '/[^A-Z0-9]/', '', $vat ) );
    $vat = ltrim( preg_replace( '/^SM/', '', $vat ), '0' );

    return (bool) preg_match( '/^\d{1,5}$/', $vat );
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
            return ( bool ) preg_match( '/^(AL)?([A-M]\d{8}[A-Z])$/', $vat_number );
        case 'AD': // Andorra.
            return ( bool ) preg_match( '/^(AD)?([ACDEFGLOPU]\d{6}[A-Z])$/', $vat_number );
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
        case 'BY': // Bielorrusia. 
            return ( bool ) preg_match( '/^(BY)?([0-9A-Z]{2}\d{7})$/', $vat_number );
        case 'CO': // Colombia.
            return ( bool ) preg_match( '/^(CO)?(\d{9,16})$/', $vat_number );
        case 'PE': // Perú.
            return ( bool ) preg_match( '/^(PE)?(\d{11})$/', $vat_number );
        case 'UY': // Uruguay.
            return ( bool ) preg_match( '/^(UY)?(\d{12})$/', $vat_number );
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
            return ( bool ) preg_match( '/^(FR)?([0-9A-HJ-NP-Z]{2}\d{9})$/', $vat_number );
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
                preg_match( '/^(IE)?(\d[A-Z\*\+]\d{5}[A-W])$/', $vat_number ) ||
                preg_match( '/^(IE)?(\d{7}[A-W][A-W])$/', $vat_number );
        case 'IS': // Islandia. 
            return ( bool ) preg_match( '/^(IS)?(\d{5,6})$/', $vat_number );
        case 'IT': // Italia.
            return ( bool ) preg_match( '/^(IT)?(\d{11}|[A-Z]{6}[0-9LMNPQRSTUV]{2}[ABCDEHLMPRST][0-9LMNPQRSTUV]{2}[A-Z][0-9LMNPQRSTUV]{3}[A-Z])$/', $vat_number );
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
            return ( bool ) preg_match( '/^(MD)?(\d{13})$/', $vat_number );
        case 'ME': // Montenegro. 
            return ( bool ) preg_match( '/^(ME)?(\d{8})$/', $vat_number );
        case 'MK': // Macedonia del Norte. 
            return ( bool ) preg_match( '/^(MK)?(\d{13})$/', $vat_number );
        case 'MT': // Malta.
            return ( bool ) preg_match( '/^(MT)?([1-9]\d{7})$/', $vat_number );
        case 'MX': // México.
            return ( bool ) preg_match( '/^(MX)?([A-Z&]{3,4}\d{6}[A-Z0-9]{3})$/', $vat_number );
        case 'NL': // Países Bajos.
            // No tiene validador propio porque el número no lleva dígito de control: el
            // módulo 11 («elfproef») sólo cuadra en los números derivados del número de
            // identificación personal, y rechaza números reales y en vigor como el
            // NL004688969B75. La validez real sólo puede confirmarla VIES.
            return ( bool ) preg_match( '/^(NL)?[A-Z0-9+*]{9}B\d{2}$/', $vat_number );
        case 'NO': // Noruega. 
            return ( bool ) preg_match( '/^(NO)?(\d{9})(MVA)?$/', $vat_number );
        case 'PL': // Polonia. 
            return ( bool ) preg_match( '/^(PL)?(\d{10})$/', $vat_number );
        case 'PT': // Portugal. 
            return ( bool ) preg_match( '/^(PT)?([1-9]\d{8})$/', $vat_number );
        case 'RO': // Rumanía. 
            return ( bool ) preg_match( '/^(RO)?([1-9]\d{1,9})$/', $vat_number );
        case 'RS': // Serbia. 
            return ( bool ) preg_match( '/^(RS)?(\d{9})$/', $vat_number );
        case 'SE': // Suecia. 
            return ( bool ) preg_match( '/^(SE)?(\d{10}\d{2})$/', $vat_number );
        case 'SI': // Eslovenia. 
            return ( bool ) preg_match( '/^(SI)?([1-9]\d{7,8})$/', $vat_number );
        case 'SK': // República Eslovaca.
            return ( bool ) preg_match( '/^(SK)?([1-9]\d{9})$/', $vat_number );
        case 'XI': // Irlanda del Norte: sus números se emiten dentro del sistema británico.
            return ( bool ) preg_match( '/^(XI)?(\d{9})$/', $vat_number ) ||
                preg_match( '/^(XI)?(\d{12})$/', $vat_number ) ||
                preg_match( '/^(XI)?(GD\d{3})$/', $vat_number ) ||
                preg_match( '/^(XI)?(HA\d{3})$/', $vat_number );
        case 'SM': // San Marino.
            return ( bool ) preg_match( '/^(SM)?(\d{5})$/', $vat_number );
        case 'UA': // Ucrania.
            return ( bool ) preg_match( '/^(UA)?(\d{8})$/', $vat_number );
        default:
            return false;
    }
}
