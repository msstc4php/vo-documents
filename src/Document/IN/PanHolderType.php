<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Document\IN;

/**
 * The status of the PAN holder, the fourth character of the number (Income Tax Department tutorial "Permanent Account
 * Number (PAN)": "The fourth character of PAN represents the status of the PAN holder"). The backing value is that
 * character.
 *
 * @see https://github.com/msstc4php/vo-documents/blob/main/docs/sources/IN.md "PAN"
 */
enum PanHolderType: string
{
    case Individual = 'P';
    case Company = 'C';
    case HinduUndividedFamily = 'H';
    case AssociationOfPersons = 'A';
    case BodyOfIndividuals = 'B';
    case GovernmentAgency = 'G';
    case ArtificialJuridicalPerson = 'J';
    case LocalAuthority = 'L';
    case FirmOrLimitedLiabilityPartnership = 'F';
    case Trust = 'T';
}
