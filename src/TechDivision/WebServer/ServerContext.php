<?php
/**
 * \TechDivision\WebServer\ServerContext
 *
 * PHP version 5
 *
 * @category  Webserver
 * @package   TechDivision_WebServer
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_WebServer
 */

namespace TechDivision\WebServer;

use TechDivision\WebServer\ConnectionPool;
use TechDivision\WebServer\Exceptions\ConnectionHandlerNotFoundException;
use TechDivision\WebServer\Exceptions\ModuleNotFoundException;
use TechDivision\WebServer\Interfaces\ConfigInterface;
use TechDivision\WebServer\Interfaces\ServerConfigurationInterface;
use TechDivision\WebServer\Interfaces\ServerContextInterface;
use TechDivision\WebServer\Modules\CoreModule;
use TechDivision\WebServer\Modules\DirectoryModule;
use TechDivision\WebServer\Sockets\SocketInterface;
use TechDivision\WebServer\Interfaces\PoolInterface;

/**
 * Class ServerContext
 *
 * @category  Webserver
 * @package   TechDivision_WebServer
 * @author    Johann Zelger <jz@techdivision.com>
 * @copyright 2014 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/TechDivision_WebServer
 */
class ServerContext implements ServerContextInterface
{

    /**
     * Hold's the config instance
     *
     * @var \TechDivision\WebServer\Interfaces\ServerConfigurationInterface
     */
    protected $serverConfig;

    /**
     * Hold's an array of modules defined in config
     *
     * @var array
     */
    protected $modules = array();

    /**
     * Hold's an array of connection handlers defined in config
     *
     * @var array
     */
    protected $connectionHandlers = array();

    /**
     * Initialises the server context
     *
     * @param \TechDivision\WebServer\Interfaces\ServerConfigurationInterface $serverConfig The servers configuration
     *
     * @return void
     */
    public function init(ServerConfigurationInterface $serverConfig)
    {
        // set configuration
        $this->serverConfig = $serverConfig;

        // initiate server connection handlers
        $connectionHandlers = $this->getServerConfig()->getConnectionHandlers();
        foreach ($connectionHandlers as $connectionHandlerType) {
            if (!class_exists($connectionHandlerType)) {
                throw new ConnectionHandlerNotFoundException($connectionHandlerType);
            }
            $this->connectionHandlers[$connectionHandlerType] = new $connectionHandlerType();
            $this->connectionHandlers[$connectionHandlerType]->init($this);
        }

        // initiate server modules
        $modules = $this->getServerConfig()->getModules();
        foreach ($modules as $moduleType) {
            if (!class_exists($moduleType)) {
                throw new ModuleNotFoundException($moduleType);
            }
            $this->modules[$moduleType] = new $moduleType();
            $this->modules[$moduleType]->init();
        }
    }

    /**
     * Return's the server config instance
     *
     * @return \TechDivision\WebServer\Interfaces\ServerConfigurationInterface The server config instance
     */
    public function getServerConfig()
    {
        return $this->serverConfig;
    }

    /**
     * Return's the server connection instance
     *
     * @param resource $connectionResource The socket resource
     *
     * @return SocketInterface The server connection instance
     */
    public function getConnectionInstance($connectionResource)
    {
        $socketType = $this->getServerConfig()->getSocketType();
        return $socketType::getInstance($connectionResource);
    }

    /**
     * Return's an array of modules
     *
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Return's an array of pre init connection handler instances
     *
     * @return array
     */
    public function getConnectionHandlers()
    {
        return $this->connectionHandlers;
    }
}
