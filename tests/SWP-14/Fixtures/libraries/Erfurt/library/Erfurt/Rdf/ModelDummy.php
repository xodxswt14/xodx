<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ModelDummy
{
    public function sparqlQuery($query, $options = array())
    {
        $result = array();
        //for further info about subUri check UserControllerTest.php
        //$result[0]['suburi'] = 'http://127.0.0.1:8080/&c=ressource&id=60743c43fe6902335feb85548af6fe8f';
        $result[0]['suburi'] = 'validSubUri';
    }
    
    public function deleteMultipleStatements(array $statements, $useAc = true)
    {
    }
}