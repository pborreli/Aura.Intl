<?php
/**
 * 
 * This file is part of the Aura Project for PHP.
 * 
 * @package Aura.Intl
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Intl;

/**
 * 
 * A ServiceLocator implementation for loading and retaining translator objects.
 * 
 * @package Aura.Intl
 * 
 */
class TranslatorLocator
{
    /**
     * 
     * A registry to retain translator objects.
     * 
     * @var array
     * 
     */
    protected $registry;

    /**
     * 
     * The current locale code.
     * 
     * @var string
     * 
     */
    protected $locale;

    /**
     *
     * @var \Aura\Intl\Translator\Factory
     */
    protected $factory;

    /**
     *
     * @var string
     */
    protected $packages;

    /**
     *
     * @var \Aura\Intl\Formatter\FormatterInterface
     */
    protected $formatter;

    /**
     * 
     * Constructor.
     * 
     * @param PackagesInterface $packages
     * 
     * @param FormatterLocator $formatters
     * 
     * @param TranslatorFactory $factory A translator factory to
     * create translator objects for the locale and package.
     * 
     * @param type $locale
     */
    public function __construct(
        PackageLocator $packages,
        FormatterLocator $formatters,
        TranslatorFactory $factory,
        $locale
    ) {
        $this->packages = $packages;
        $this->factory = $factory;
        $this->formatters = $formatters;
        $this->setLocale($locale);
    }

    /**
     * 
     * Sets the current locale code.
     * 
     * @param string $locale The new locale code.
     * 
     * @return void
     * 
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * 
     * Returns the current locale code.
     * 
     * @return string
     * 
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * 
     * The TranslatorFactory object
     * 
     * @return TranslatorFactory
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * 
     * An object of type PackagesInterface
     * 
     * @return PackagesInterface
     * 
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * 
     * object of type FormatterLocator
     * 
     * @return FormatterLocator
     * 
     */
    public function getFormatters()
    {
        return $this->formatters;
    }

    /**
     * 
     * Gets a translator from the registry by package for the current locale.
     * 
     * @param string $package The translator package to retrieve.
     * 
     * @param string $package The locale to use; if empty, uses the current
     * locale.
     * 
     * @return TranslatorInterface A translator object.
     * 
     */
    public function get($name, $locale = null)
    {
        if (! $name) {
            return null;
        }

        if (! $locale) {
            $locale = $this->getLocale();
        }

        if (! isset($this->registry[$name][$locale])) {
            
            // get the package descriptor
            $package = $this->packages->get($name, $locale);
            
            // build a translator; note the recursive nature of the
            // 'fallback' param at the very end.
            $translator = $this->factory->newInstance(
                $locale,
                $package->getMessages(),
                $this->formatters->get($package->getFormatter()),
                $this->get($package->getFallback(), $locale)
            );
            
            // retain in the registry
            $this->registry[$name][$locale] = $translator;
        }

        return $this->registry[$name][$locale];
    }
}