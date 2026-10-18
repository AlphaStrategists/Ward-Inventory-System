<?php

namespace App\Http\Controllers;

/**
 * Injection Antibiotics page: same behaviour as the Injection page,
 * scoped to the "Injection Antibiotics" category.
 */
class InjectionAntibioticController extends InjectionController
{
    protected string $categoryName = 'Injection Antibiotics';

    protected string $categoryDescription = 'Antibiotic injections';

    protected string $view = 'injection-antibiotics.index';
}
