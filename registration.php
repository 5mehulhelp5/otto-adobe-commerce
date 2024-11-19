<?php

use M2E\Otto\Helper\Module;
use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(ComponentRegistrar::MODULE, Module::IDENTIFIER, __DIR__);
