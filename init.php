<?php

// Bobospay singleton
require(dirname(__FILE__) . '/src/Bobospay.php');

// Utilities
require(dirname(__FILE__) . '/src/Util/Inflector.php');
require(dirname(__FILE__) . '/src/Util/Util.php');
require(dirname(__FILE__) . '/src/Util/RandomGenerator.php');

// HttpClient
require(dirname(__FILE__) . '/src/Rest/HttpClient.php');
require(dirname(__FILE__) . '/src/Rest/Requestor.php');

// Errors
require(dirname(__FILE__) . '/src/Exception/BobospayException.php');
require(dirname(__FILE__) . '/src/Exception/ApiConnection.php');
require(dirname(__FILE__) . '/src/Exception/InvalidRequest.php');
require(dirname(__FILE__) . '/src/Exception/SignatureVerification.php');

// API operations
require(dirname(__FILE__) . '/src/Api/All.php');
require(dirname(__FILE__) . '/src/Api/Create.php');
require(dirname(__FILE__) . '/src/Api/Search.php');
require(dirname(__FILE__) . '/src/Api/Delete.php');
require(dirname(__FILE__) . '/src/Api/Request.php');
require(dirname(__FILE__) . '/src/Api/Retrieve.php');
require(dirname(__FILE__) . '/src/Api/Save.php');
require(dirname(__FILE__) . '/src/Api/Update.php');

// Plumbing
require(dirname(__FILE__) . '/src/Common/BobospayObject.php');

// Bobospay API Resources
require(dirname(__FILE__) . '/src/Currency.php');
require(dirname(__FILE__) . '/src/Customer.php');
require(dirname(__FILE__) . '/src/Transaction.php');
