<?php

namespace App\Enums;

/**
 * Pagina's waarop een zwevende pill kan staan, met per pagina de secties in
 * volgorde van boven naar beneden. De sectie-keys komen overeen met de
 * data-pill-section-attributen in de Vue-pagina's.
 */
enum PillPage: string
{
    case Home = 'home';
    case HomeV2 = 'home-v2';
    case About = 'about';
    case CxbyEx = 'cxbyex';
    case Industries = 'industries';
    case Jobs = 'jobs';
    case Contact = 'contact';
    case CasesIndex = 'cases-index';
    case CasesShow = 'cases-show';
    case BlogIndex = 'blog-index';
    case BlogShow = 'blog-show';

    public function label(): string
    {
        return match ($this) {
            self::Home => 'Homepage',
            self::HomeV2 => 'Homepage V2 (cases vóór services)',
            self::About => 'About',
            self::CxbyEx => 'CXbyEX',
            self::Industries => 'Industries',
            self::Jobs => 'Jobs',
            self::Contact => 'Contact',
            self::CasesIndex => 'Cases (overzicht)',
            self::CasesShow => 'Case (detailpagina, alle cases)',
            self::BlogIndex => 'Blog (overzicht)',
            self::BlogShow => 'Blog (artikel, alle artikelen)',
        };
    }

    /**
     * @return array<string, string> sectie-key => label, van boven naar beneden
     */
    public function sections(): array
    {
        return match ($this) {
            self::Home => [
                'hero' => 'Hero',
                'about' => 'About (gele sectie)',
                'services' => 'Services',
                'cases' => 'Cases',
                'testimonial' => 'Testimonial',
                'team' => 'Team',
            ],
            self::HomeV2 => [
                'hero' => 'Hero',
                'cases' => 'Cases',
                'about' => 'Services-intro (gele sectie)',
                'services' => 'Services',
                'testimonial' => 'Testimonial',
                'team' => 'Team',
            ],
            self::About => [
                'hero' => 'Hero',
                'credible' => 'Credible',
                'growth' => 'Growth',
                'dimensions' => 'Brand dimensions',
                'ancient' => 'Ancient',
                'approach' => 'Approach',
                'narrative' => 'Narrative',
                'services' => 'Services',
                'international' => 'International',
                'team-photos' => "Teamfoto's",
            ],
            self::CxbyEx => [
                'hero' => 'Hero',
                'narrative' => 'Narrative-tekst',
                'case' => 'Case-blok',
                'body' => 'Tekstkolommen',
                'quote' => 'Quote',
                'steps' => 'From story to strategy to action',
                'brands' => "Merken / logo's",
            ],
            self::Industries => [
                'hero' => 'Hero',
                'industries' => 'Branches',
            ],
            self::Jobs => [
                'hero' => 'Hero',
                'jobs' => 'Vacatures',
            ],
            self::Contact => [
                'hero' => 'Hero',
                'intro' => 'Intro',
                'team' => 'Team',
                'clients' => "Klantlogo's",
                'join' => 'Join us',
            ],
            self::CasesIndex => [
                'hero' => 'Titel',
                'filter' => 'Filter',
                'cases' => 'Cases',
            ],
            self::CasesShow => [
                'hero' => 'Hero',
                'meta' => 'Meta',
                'overview' => 'Overview',
                'gallery' => 'Galerij',
                'highlight' => 'Highlight',
                'campaign' => 'Campagne',
                'story' => 'Story',
                'pre-callout-gallery' => 'Galerij voor callout',
                'callout' => 'Callout',
                'secondary-story' => 'Tweede story',
                'results' => 'Resultaten',
                'optional-panel' => 'Optioneel paneel',
                'more-cases' => 'Meer cases',
            ],
            self::BlogIndex => [
                'hero' => 'Titel en filter',
                'posts' => 'Artikelen',
            ],
            self::BlogShow => [
                'article' => 'Artikel',
                'more' => 'Meer artikelen',
            ],
        };
    }

    public function sectionLabel(?string $section): ?string
    {
        return $section === null ? null : ($this->sections()[$section] ?? $section);
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $page) {
            $options[$page->value] = $page->label();
        }

        return $options;
    }
}
