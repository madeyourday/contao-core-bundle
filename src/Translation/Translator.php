<?php

/*
 * This file is part of Contao.
 *
 * Copyright (c) 2005-2017 Leo Feyer
 *
 * @license LGPL-3.0+
 */

namespace Contao\CoreBundle\Translation;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\System;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Translator.
 *
 * @author Martin Auswöger <martin@auswoeger.com>
 */
class Translator implements TranslatorInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * @param TranslatorInterface      $translator The translator to decorate
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(TranslatorInterface $translator, ContaoFrameworkInterface $framework)
    {
        $this->translator = $translator;
        $this->framework = $framework;
    }

    /**
     * {@inheritdoc}
     *
     * Gets the translation from Contao’s $GLOBALS['TL_LANG'] array if the message domain starts with
     * "contao_". The locale parameter is ignored in this case.
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        // Forward to the default translator
        if (null === $domain || strncmp($domain, 'contao_', 7) !== 0) {
            return $this->translator->trans($id, $parameters, $domain, $locale);
        }

        $domain = substr($domain, 7);

        $this->framework->initialize();
        $this->loadLanguageFile($domain);

        $translated = $this->getFromGlobals($id, $domain);

        if (null === $translated) {
            return $id;
        }

        if (!empty($parameters)) {
            $translated = vsprintf($translated, $parameters);
        }

        return $translated;
    }

    /**
     * {@inheritdoc}
     */
    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        // Forward to the default translator
        return $this->translator->transChoice($id, $number, $parameters, $domain, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        // Forward to the default translator
        return $this->translator->setLocale($locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        // Forward to the default translator
        return $this->translator->getLocale();
    }

    /**
     * Returns the labels from the $GLOBALS['TL_LANG'] array.
     *
     * @param string $id     Message id, e.g. "MSC.view"
     * @param string $domain Message domain, e.g. "messages" or "tl_content"
     *
     * @return string|null
     */
    private function getFromGlobals(string $id, string $domain)
    {
        if ('default' !== $domain) {
            $id = $domain.'.'.$id;
        }

        // Split the ID into chunks allowing escaped dots (\.) and backslashes (\\)
        preg_match_all('/(?:\\\\[.\\\\]|[^.])++/s', $id, $matches);
        $parts = preg_replace('/\\\\([.\\\\])/s', '$1', $matches[0]);

        $item = &$GLOBALS['TL_LANG'];

        foreach ($parts as $part) {
            if (!isset($item[$part])) {
                return null;
            }

            $item = &$item[$part];
        }

        return $item;
    }

    /**
     * Loads a Contao framework language file.
     *
     * @param string $name
     */
    private function loadLanguageFile(string $name)
    {
        /** @var System $system */
        $system = $this->framework->getAdapter(System::class);

        $system->loadLanguageFile($name);
    }
}
