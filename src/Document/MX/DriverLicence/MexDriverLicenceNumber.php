<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\MX\DriverLicence;

use MSSTC4PHP\ValueObject\Documents\Document\MX\Jurisdiction;
use MSSTC4PHP\ValueObject\Documents\Internal\NumberShapes;
use Override;

/**
 * Estado de México: twelve digits. The validator linked from the Secretaría de Movilidad's "Validación de
 * licencias" page states "El Número de Licencia es de 12 dígitos" and "debe contener solo números" (pattern
 * `^\d{12}$`); its predecessor asked for "los DOCE números de su licencia (frente de su plástico)". Format only: no
 * encoding or check digit is published, and no display form, so there is no `formatted()`.
 *
 * @see https://smovilidad.edomex.gob.mx/validacion_de_licencias Secretaría de Movilidad, "Validación de licencias"
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/MX.md "State driving licences (Task 6b)"
 */
final class MexDriverLicenceNumber extends AbstractDriverLicenceNumber
{
    public const int MAX_LENGTH = 12;

    #[Override]
    public function jurisdiction(): Jurisdiction
    {
        return Jurisdiction::MEX;
    }

    #[Override]
    protected static function shapes(): NumberShapes
    {
        return new NumberShapes('[0-9]{12}');
    }
}
