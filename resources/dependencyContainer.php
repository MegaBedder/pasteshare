<?php
/**
 * This file is the dependency container for the pasteshare application
 * @license MIT
 */
$deps = new Pimple();

/**
 * Site Configuration Key
 * @return object An instance of the config class representing the site config
 */
$deps["siteConfig"] = $deps->share(function () {
    return new \pasteshare\Config(__dir__ . "/../configs/site.json");
});

/**
 * MongoClient key
 * This is the mongo client
 *
 * @param object $deps The dependency container
 * @return object The mongoclient object
 */
$deps["mongoClient"] = $deps->share(function ($deps) {
    $config = $deps["siteConfig"];
    $servers = "mongodb://" . implode(",", $config->mongoDb->server);
    
    $options = [];
    $options["connectTimeoutMS"] = $config->mongoDb->connectTimeoutMS;
    $options["socketTimeoutMS"] = $config->mongoDb->socketTimeoutMS;
    if (strlen($config->mongoDb->replicaSet) > 0) {
        $options["replicaSet"] = $config->mongoDb->replicaSet;
    }
    
    $client = new \MongoClient($servers, $options);
    $client->setReadPreference(\MongoClient::RP_SECONDARY_PREFERRED);
    
    return $client;
});

/**
 * MongoDM Key
 * This is the mongo document manager
 *
 * @param object $deps The dependency container
 * @return object The doctrine mongo document manager object
 */
$deps["mongoDm"] = $deps->share(function ($deps) {

    /** Register the annotation class */
    \Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver::registerAnnotationClasses();

    /** Set up the document manager configuration */
    $config = new \Doctrine\ODM\MongoDB\Configuration();
    $config->setDefaultDB($deps["siteConfig"]->mongoDb->database);
    $config->setProxyDir("/tmp/pasteshare/proxies");
    $config->setProxyNamespace("Proxies");
    $config->setHydratorDir("/tmp/pasteshare/hydrators");
    $config->setHydratorNamespace("Hydrators");
    $config->setMetadataDriverImpl(
        \Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver::create(
            "{$deps["siteConfig"]->paths->app_root}/models/Sid"
        )
    );

    try {
        $client = $deps["mongoClient"];
    } catch (\MongoConnectionException $e) {
        throw new \RuntimeException("Database unavailable");
    }
    
    /** Instantiate and return the document manager */
    return \Doctrine\ODM\MongoDB\DocumentManager::create(
        new \Doctrine\MongoDB\Connection($deps["mongoClient"]),
        $config
    );
});
