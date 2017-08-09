<?php if(! defined('WWA_PLUGIN_PREFIX')) return;
class WwaScheduler

{

    private static $_cronTasks = array();

    public static function registerCronTask($cronActionName, $callback, $interval = 'daily')

    {

        if(! is_callable($callback)) { return; }


        if(! self::canRegisterCronTask()){

            self::registerTask($callback);

            return;

        }

        $interval = strtolower($interval);

        if(empty($interval)){ $interval = 'daily'; }

        else{

            $timeIntervals = wp_get_schedules();

            if(! array_key_exists($interval, $timeIntervals)){

                $interval = 'daily';

            }

        }

        add_action($cronActionName, $callback);

        if ( ! wp_next_scheduled($cronActionName) ) {

            wp_schedule_event( time(), $interval, $cronActionName );

            array_push(self::$_cronTasks, $cronActionName);

        }

    }



    public static function unregisterCronTask($cronActionName){

        wp_clear_scheduled_hook($cronActionName);

        if(! empty(self::$_cronTasks)){

            if(isset(self::$_cronTasks[$cronActionName])){

                unset(self::$_cronTasks[$cronActionName]);

            }

        }

    }



    public static function unregisterCronTasks(){

        if(! empty(self::$_cronTasks)){

            foreach (self::$_cronTasks as $task) {

                wp_clear_scheduled_hook($task);

            }

            self::$_cronTasks = array();

        }

    }


    public static function canRegisterCronTask(){ return ((defined('DISABLE_WP_CRON') && 'DISABLE_WP_CRON') ? false : true); }


    public static function registerTask($callback, $wpActionName = '') {

        if(! empty($wpActionName)){

            add_action($wpActionName, $callback);

        }

        else {

            if(is_callable($callback)){

                call_user_func($callback);

            }

        }

    }

    public static function registerClassTasks($className, $onlyWithPrefix = '')

    {

        $_class = new ReflectionClass($className);

        $methods = $_class->getMethods();

        if(! empty($methods)){

            $pLength = strlen($onlyWithPrefix);

            foreach($methods as $_method){

                $method = $_method->name;


                if($pLength > 0){

                    $search = substr($method, 0, $pLength);

                    if(strcasecmp($search,$onlyWithPrefix) == 0){

                        call_user_func(array($className, $method));

                    }

                }

                else { call_user_func(array($className, $method)); }

            }

        }

    }



}