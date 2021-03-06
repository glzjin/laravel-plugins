<?php
namespace Oneso\LaravelPlugins;

abstract class Plugin
{
    protected $app;

    /**
     * The Plugin Name.
     *
     * @var string
     */
    public $name;

    /**
     * @var $this
     */
    private $reflector = null;

    /**
     * Plugin constructor.
     *
     * @param $app
     */
    public function __construct($app)
    {
        $this->app = $app;

        $this->checkPluginName();
    }

    abstract public function boot();

    /**
     * Check for empty plugin name.
     *
     * @throws \InvalidArgumentException
     */
    private function checkPluginName()
    {
        if (!$this->name) {
            throw new \InvalidArgumentException('Missing Plugin name.');
        }
    }

    /**
     * Returns the view namespace
     * Eg: 'plugin:articles'
     *
     * @return string
     */
    protected function getViewNamespace()
    {
        return 'plugin:' . $this->name;
    }

    /**
     * Add a view namespace for this plugin.
     * Eg: view("plugin:articles::{view_name}")
     *
     * @param string $path
     */
    protected function enableViews($path = 'src/views')
    {
        $this->app['view']->addNamespace(
            $this->getViewNamespace(),
            $this->getPluginPath() . DIRECTORY_SEPARATOR . $path
        );
    }

    /**
     * Enable routes for this plugin.
     *
     * @param string $path
     */
    protected function enableRoutes($path = 'src/routes.php')
    {
        $this->app->group(['namespace' => $this->getPluginControllerNamespace()], function ($app) use ($path) {
            require $this->getPluginPath() . DIRECTORY_SEPARATOR . $path;
        });
    }

    /**
     * @return string
     */
    public function getPluginPath()
    {
        $reflector = $this->getReflector();
        $fileName  = $reflector->getFileName();

        return dirname($fileName);
    }

    /**
     * @return string
     */
    protected function getPluginControllerNamespace()
    {
        $reflector = $this->getReflector();
        $baseDir   = str_replace($reflector->getShortName(), '', $reflector->getName());

        return $baseDir . 'Http\\Controllers';
    }

    /**
     * @return \ReflectionClass
     */
    private function getReflector()
    {
        if (is_null($this->reflector)) {
            $this->reflector = new \ReflectionClass($this);
        }

        return $this->reflector;
    }

    /**
     * Returns a plugin view
     *
     * @param $view
     * @return \Illuminate\View\View
     */
    protected function view($view)
    {
        return view($this->getViewNamespace() . '::' . $view);
    }
}