<?php if(! WwaUtil::canLoad()) { return; } ?>

<ul class="acx-common-list">

    <?php


        $class = new ReflectionClass('WwaInfo');

        $methods = $class->getMethods();

        if(! empty($methods)){

            foreach($methods as $method){

                echo '<li><p>'.call_user_func(array($method->class, $method->name)).'</p></li>';

            }

        }

    ?>

</ul>

