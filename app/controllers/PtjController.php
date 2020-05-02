<?php

class PtjController extends ControllerBase
{

    public function indexAction()
    {
    	$this->view->disable();
        
        if (!$this->request->isPost()) {
            return $this->http405();
        }
        
        // Check parameters
        if (isset($_POST["story"]) == false || isset($_POST["chars"]) == false || isset($_POST["props"]) == false || isset($_POST["sets"]) == false)
        {
            return $this->http400("Parameter missing");
        }

        ControlTerm::InitPreTerms($_POST["chars"], $_POST["props"], $_POST["sets"]);
        $input = ControlTerm::checkInControlTerms($_POST["story"]);

        // echo $input . "\r\n";

        $test = new CkipSelenium();
        $ret = $test->TheSeleniumTest($input);

//         $ret = "艾瑪(Nb)　從(P)　\"(FW)　咖啡廳(Nc)　\"(FW)　走出(VCL)　來到(VCL)　馬路(Na)　上(Ncd)　。(PERIODCATEGORY)\r\n
// ----------------------------------------------------------------------------------------------------------------------------------\r\n
// \r\n
// ";

        // echo $ret . "\r\n\r\n";

        $test->TeardownTest();

        $pa = new PosAnalysis();

        // from POS tagging result to term->POS mapping array
        $l_lkv = $pa->posConvertToJsonObj(trim($ret));
        $lJson = [];

        for ($i = 0; $i < count($l_lkv); $i++)
        {
            ControlTerm::postProcess($l_lkv[$i]);

            // merge controller term
            $lKV = $l_lkv[$i];
            for ($j = count($lKV) - 1; $j >=0 ; $j--)
            {
                switch (strtoupper($lKV[$j][1]))
                {
                    case "FW":
                        if ($lKV[$j][0] == "\"")
                        {
                            for ($itmp = $j-1; $itmp >= 0; $itmp-- )
                            {
                                if ($lKV[$itmp][0] == "\"")
                                {
                                    $str_newterm = "";
                                    for ($iconcat = $itmp+1;$iconcat < $j; $iconcat++)
                                    {
                                        $str_newterm .= $lKV[$iconcat][0];
                                    }
                                    if (ControlTerm::_inCtrlTerms($str_newterm, "char"))
                                    {
                                        $lKV[$itmp+1][0] = $str_newterm;
                                        $lKV[$itmp+1][1] = "NB";

                                        for ($iconcat = $itmp+2;$iconcat < $j; $iconcat++)
                                        {
                                            unset($lKV[$iconcat]);
                                        }
                                    }
                                    else if (ControlTerm::_inCtrlTerms($str_newterm, "props"))
                                    {
                                        $lKV[$itmp+1][0] = $str_newterm;
                                        $lKV[$itmp+1][1] = "NA";

                                        for ($iconcat = $itmp+2;$iconcat < $j; $iconcat++)
                                        {
                                            unset($lKV[$iconcat]);
                                        }
                                    }
                                    else if (ControlTerm::_inCtrlTerms($str_newterm, "sets"))
                                    {
                                        $lKV[$itmp+1][0] = $str_newterm;
                                        $lKV[$itmp+1][1] = "NC";

                                        for ($iconcat = $itmp+2;$iconcat < $j; $iconcat++)
                                        {
                                            unset($lKV[$iconcat]);
                                        }
                                    }

                                    $j = $itmp;
                                    break;
                                }
                            }
                        }
                        break;
                }
            }
            $l_lkv[$i] = array_values($lKV);

            var_dump($l_lkv[$i]);

            array_push($lJson, json_decode($pa->cKVlistToJson_v2($l_lkv[$i], FindModelFilePref::YesAndSimilar, DbMode::Remote)));
        }

        // var_dump(json_encode($lJson));

        $resp = new \Phalcon\Http\Response();
        $resp->setStatusCode(200, "OK");
        $resp->setHeader("Content-Type", "application/json");
        $resp->setHeader("Access-Control-Allow-Origin", "*");
        $resp->setHeader('Access-Control-Allow-Headers', 'X-Requested-With');
        $resp->sendHeaders();
        // $resp->setContent(json_encode($lJson, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT));
        $resp->setContent(json_encode($lJson));
        $resp->send();
        return;
    }

}


// class FindModelFilePref
// {
//     const No = 1;
//     const Yes = 2;
//     const YesAndSimilar = 3;
// }