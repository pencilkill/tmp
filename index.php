<?php

class bootstrap
{

    public static $aliases = array(
        '@doc' => __DIR__
    );

    public static function init()
    {
        self::setAlias('@a', self::getAlias('@doc') . DIRECTORY_SEPARATOR . 'a');
        self::setAlias('@b', self::getAlias('@doc') . DIRECTORY_SEPARATOR . 'b');
        self::setAlias('@c', self::getAlias('@doc') . DIRECTORY_SEPARATOR . 'b' . DIRECTORY_SEPARATOR . 'c');
        
        spl_autoload_register(array(
            __CLASS__,
            'autoLoad'
        ));
    }

    public static function getAlias($alias, $throwException = true)
    {
        if (strncmp($alias, '@', 1))
        {
            // not an alias
            return $alias;
        }
        
        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);
        
        if (isset(static::$aliases[$root]))
        {
            if (is_string(static::$aliases[$root]))
            {
                return $pos === false ? static::$aliases[$root] : static::$aliases[$root] . substr($alias, $pos);
            }
            else
            {
                foreach (static::$aliases[$root] as $name => $path)
                {
                    if (strpos($alias . '/', $name . '/') === 0)
                    {
                        return $path . substr($alias, strlen($name));
                    }
                }
            }
        }
        
        if ($throwException)
        {
            throw new InvalidParamException("Invalid path alias: $alias");
        }
        else
        {
            return false;
        }
    }

    public static function setAlias($alias, $path)
    {
        if (strncmp($alias, '@', 1))
        {
            $alias = '@' . $alias;
        }
        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);
        if ($path !== null)
        {
            $path = strncmp($path, '@', 1) ? rtrim($path, '\\/') : static::getAlias($path);
            if (! isset(static::$aliases[$root]))
            {
                if ($pos === false)
                {
                    static::$aliases[$root] = $path;
                }
                else
                {
                    static::$aliases[$root] = [
                        $alias => $path
                    ];
                }
            }
            elseif (is_string(static::$aliases[$root]))
            {
                if ($pos === false)
                {
                    static::$aliases[$root] = $path;
                }
                else
                {
                    static::$aliases[$root] = [
                        $alias => $path,
                        $root => static::$aliases[$root]
                    ];
                }
            }
            else
            {
                static::$aliases[$root][$alias] = $path;
                krsort(static::$aliases[$root]);
            }
        }
        elseif (isset(static::$aliases[$root]))
        {
            if (is_array(static::$aliases[$root]))
            {
                unset(static::$aliases[$root][$alias]);
            }
            elseif ($pos === false)
            {
                unset(static::$aliases[$root]);
            }
        }
    }

    public static function autoLoad($class)
    {
        if (strpos($class, '@') === false)
        {
            $class = '@' . $class;
        }
        if (strpos($class, '_')) {
        	$class = strtolower($class);
        }
        
        $file = static::getAlias(strtr($class, array('\\' => '/', '_' => '/')), false);
        if (strpos($class, '_') && ($pos = strrpos($file, '/')))
        {
            $file = substr($file, 0, $pos) . '/' . ltrim($class, '@');
        }
        if($file)
        {
            $file .= '.php';
        }
        
        is_file($file) && include ($file);
    }
}

bootstrap::init();
A_B_C::init();
A_A::init();
b\B::init();
b\c\C::init();