<?php

declare(strict_types=1);

namespace MSSTC4PHP\ValueObject\Documents\Name\NL;

use MSSTC4PHP\ValueObject\Documents\Name\Latin\AbstractLatinNamePart;
use Override;

/**
 * Letters of the BRP character set: `A-Z`, `a-z`, the letters of LO BRP table 5.2 and the letter-diacritic
 * combinations of table 5.3, case as listed (`İ` and `ı`, `ð` without a capital). The ligature `ĳ` is not one of them
 * ("Niet opgenomen tekens mogen niet gebruikt worden. Dat betreft onder andere de Hollandse IJ"): it is written `ij`.
 * Words are joined by a hyphen, an apostrophe or a space; a prefix of table 36 may start a word with an apostrophe
 * (`'t Hart`, `van 't Hof`) or end one before a space (`d' Ancona`). See docs/sources/NL.md "Names".
 *
 * @see https://rvig.nl/sites/default/files/2025-05/Logisch%20Ontwerp%20BRP%202025.Q2.pdf Logisch Ontwerp BRP 2025.Q2,
 *      § 5.1.2 (tables 5.2, 5.3)
 * @see https://publicaties.rvig.nl/media/13287/download Landelijke tabel 36, Voorvoegseltabel
 */
abstract class AbstractDutchNamePart extends AbstractLatinNamePart
{
    private const string LETTERS = 'A-Za-z'
        . 'ÆĐĦĿŁØŒÞŦŊæđðħıŀłøœßþŧŋĸ'
        . 'ÀÁÂÃĀĂÄÅĄàáâãāăäåąĆĈĊÇČćĉċçčĎďÈÉÊĒĖËĘĚèéêēėëęěĜĞĠĢĝğġģĤĥÌÍÎĨĪİÏĮìíîĩīïįĴĵĶķ'
        . 'ĹĻĽĺļľŃÑŅŇńñņňÒÓÔÕŌÖŐòóôõōöőŔŖŘŕŗřŚŜŞŠśŝşšŢŤţťÙÚÛŨŪŬÜŮŰŲùúûũūŭüůűųŴŵÝŶŸýŷÿŹŻŽźżž';

    #[Override]
    final protected function letters(): string
    {
        return self::LETTERS;
    }

    #[Override]
    final protected function word(): string
    {
        return "(?:(?<![^ ])')?[" . self::LETTERS . "]+(?:'(?= ))?";
    }
}
