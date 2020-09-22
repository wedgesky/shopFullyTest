<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FlyerController extends AbstractController
{
    CONST FILTERS_CONST = ["category","is_published"];
    /**
     * @Route("/flyers", name="flyers")
     */
    public function index(Request $request)
    {
        $result = null;
        try{
            $page = $request->query->get('page');
            $limit = $request->query->get('limit');
            $fields = $request->query->get('fields');
            $filter = $request->query->get('filter');
            $rowNo = 0;
            $fieldsRow = [];
            $filterKeys = [];
            $filtersRow = [];
            $serviceList = null;
            $rowAdded = 0;

            if(!isset($fields)){
                $fields = [];
            }else{
                $fields = explode(',', $fields);
            }

            if(!isset($limit)){
                $limit = 100;
            }

            if(!isset($page)){
                $result = new JsonResponse("PARAMETER PAGE NOT DEFINED", JsonResponse::HTTP_BAD_REQUEST);
            }

            if(isset($filter)){
                $filterKeys = array_keys($filter);

                foreach ($filterKeys as $key => $value){
                    if(!in_array($value, self::FILTERS_CONST)){
                        $result = new JsonResponse("FILTER ".$value. " NOT DEFINED", JsonResponse::HTTP_BAD_REQUEST);
                    }
                }
            }else{
                $filter = [];
            }

            $skip = ($page-1)*$limit;

            $file = $this->getParameter('file.data');

            if ( $result == null and ($fp = fopen($file, "r")) !== FALSE) {
                while (($row = fgetcsv($fp)) !== FALSE and $rowAdded<$limit) {
                    $num = count($row);

                    if($rowNo == 0){
                        $numFields = count($fields);
                        $numFilter = count($filterKeys);

                        if($numFields == 0){
                            for ($c=0; $c < $num; $c++) {
                                array_push($fieldsRow, $c);

                                $serviceList .= $row[$c].",";
                            }
                        }else{

                            for ($i=0; $i < $numFields; $i++) {
                                $found = false;

                                for ($c=0; $c < $num; $c++) {
                                    if($row[$c] == $fields[$i]){
                                        array_push($fieldsRow, $c);

                                        $found = true;
                                    }
                                }

                                if(!$found){
                                    $result = new JsonResponse("FIELD ".$fields[$i]. " NOT DEFINED", JsonResponse::HTTP_BAD_REQUEST);

                                    break;
                                }else{
                                    $serviceList .= $fields[$i].",";
                                }
                            }
                        }


                        for ($i=0; $i < $numFilter; $i++) {
                            $found = false;

                            for ($c=0; $c < $num; $c++) {
                                if($row[$c] == $filterKeys[$i]){
                                    $filtersRow = array_merge($filtersRow, array($row[$c]=>$c));

                                    $found = true;
                                }

                            }
                        }

                        $serviceList = rtrim($serviceList,",")."\n";

                    }else if($rowNo > $skip){
                        $addRow = true;

                        for ($c=0; $c < $num; $c++) {
                            foreach ($filtersRow as $key => $value){
                                if($value == $c and $filter[$key] != $row[$c]){
                                    $addRow = false;
                                }
                            }
                        }

                        if($addRow){
                            for ($c=0; $c < $num; $c++) {
                                $add = false;


                                if(count($fieldsRow)>0 ){
                                    if (in_array($c,$fieldsRow)){
                                        $add = true;
                                    }
                                }else{
                                    $add = true;
                                }

                                if($add){
                                    $serviceList.= $row[$c].",";

                                }
                            }

                            $serviceList = rtrim($serviceList,",")."\n";
                        }


                        $rowAdded++;


                    }
                    $rowNo++;


                }
                fclose($fp);
            }

            if($result == null){
                if(strlen($serviceList) == 0){
                    $result = new JsonResponse("NO DATA FOUND", JsonResponse::HTTP_NOT_FOUND);
                }else{
                    $result = new JsonResponse($serviceList, JsonResponse::HTTP_OK);
                }
            }



        }catch (\Exception $e){
            $result = new JsonResponse($e->getMessage(), JsonResponse::HTTP_BAD_REQUEST);
        } finally {
            return  $result;
        }
    }


}
