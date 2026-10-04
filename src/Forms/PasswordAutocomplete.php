<?php
declare(strict_types=1);

namespace Eightfold\HtmlBuilder\Forms;

use Stringable;

use Eightfold\HtmlBuilder\Element;
use Eightfold\HtmlBuilder\PropertyInterface;

use Eightfold\HtmlBuilder\Forms\SelectType;

enum PasswordAutocomplete: string
{
    case On              = 'on';
    case Off             = 'off';
    case CurrentPassword = 'current-password';
    case NewPassword     = 'new-password';
}
