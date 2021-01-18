<html>



<body style="font-family:monospace; font-size:18px;"><center>



  <big><b>Simulation</b></big><br/>



  <a href="index.php">Change parameters</a> -



  <a href="data1.csv">Server1 CSV</a> -



  <a href="data2.csv">Server2 CSV</a><br/>



<table  style="width: 90%; margin:60px 0; text-align:center;">

  <tr><td style="width:50%">

    <h3>Response latency</h3>

 <canvas style="margin:20px;" id="myChart1"></canvas>

</td><td style="width:50%">

  <h3>CPU utilization</h3>

<canvas style="margin:20px;" id="myChart2"></canvas>

</td></tr>

</div>





<?php





$samples = $_POST['samples'];



$minNew = $_POST['minNew'];



$maxNew = $_POST['maxNew'];



$minOut1 = $_POST['minOut1'];



$maxOut1 = $_POST['maxOut1'];



$minOut2 = $_POST['minOut2'];



$maxOut2 = $_POST['maxOut2'];



$prob1 = $_POST['prob1']; //in %, the other will be 100% - this%



//$reqCont = $_POST['reqCont']; //requests per container



$minTime = $_POST['minTime'];



$maxTime = $_POST['maxTime'];



$deployTime = $_POST['deployTime'];



$cont1 = $_POST['cont1'];



$cont2 = $_POST['cont2'];



$cpu = $_POST['cpu'];



$cpu_init_1 = $_POST['cpu_init_1'];



$cpu_init_2 = $_POST['cpu_init_2'];



$totReq = 0;



$req1 = 0;



$req2 = 0;



$newOnes = 0;



$out1 = 0;



$out2 = 0;



$outOnes = 0;



$time1 = 0;



$time2 = 0;



$timer1 = 0;



$timer2 = 0;



$rtt1 = 30;



$rtt2 = 900;



$avg_response_time= 16;



$current_cpu_1 = $cpu_init_1;



$current_cpu_2 = $cpu_init_2;



$msg = '';



$create1 = false;



$create2 = false;







$series1 = array();



$series2 = array();







$contSeries1 = array();



$contSeries2 = array();







$reqSeries1 = array();



$reqSeries2 = array();





$cpuSeries1 = array();



$cpuSeries2 = array();



?>







<table border="1" cellspacing="0" cellpadding="5" style="text-align:center;"><tr>



  <td>Epoch Time</td>



  <td>Requests</td>



  <td>Incoming</td>



  <td>Outgoing</td>



  <td>List 1</td>



  <td>Time 1</td>



  <td>Cont. 1</td>



  <td>List 2</td>



  <td>Time 2</td>



  <td>Cont. 2</td>



  <td>CPU (%) 1</td>



  <td>CPU (%) 2</td>



  <td>Outstanding req 1</td>



  <td>Outstanding req 2</td>



</tr>





<?php







for($i=0;$i<$samples;$i++){



 // new requests



   $newOnes = rand($minNew,$maxNew);



   $totReq += $newOnes;





 // completed requests per second



   $out1 = $newOnes * rand($minOut1,$maxOut1);



   $out2 = $newOnes * rand($minOut2,$maxOut2);



   $out1 = $out1 > $req1 ? 0 : $out1;



   $out2 = $out2 > $req2 ? 0 : $out2;



   $outOnes = $out1+$out2;



   $req1 -= $out1;



   $req2 -= $out2;



   $totReq -= ($out1 + $out2);

     for($j=0;$j<$newOnes;$j++)

       $which_server = 1;

       if($req1 < $req2){

        $req1++;

        $which_server = 1;

       } elseif($req1 > $req2){

         $req2++;

         $which_server = 2;

       }else{

        if($rtt1 > $rtt2){

          $req2++;

          $which_server = 2;

        } else{

          $req1++;

          $which_server = 1;

        }

      }

      if($which_server == 1){

        if ($current_cpu_1 <= 100){

          $current_cpu_1 +=  rand(0,1);

        }

        else{

          $current_cpu_1 = 70 ;

        }

      }

      else{

        if ($current_cpu_2 <= 99){

          $current_cpu_2 += rand(0, 1);

        }

        else{

         $current_cpu_2 = 70 ;

       }

      }



       //if(rand(1,100)<=$prob1) $req1++; else $req2++;



  // check if cpu usage is exceeded , if so launch a new container

  if ($current_cpu_1 > $cpu && $create1==false){

    $msg.='Start creating new container for 1<br/>';

    $create1 = true;

    $timer1 = 0;

    $cont1 = ceil($cont1 * $current_cpu_1 / $cpu); // autoscaling logic

    //$current_cpu_1 -= $totReq * rand(0.5,1);

  }



  if ($current_cpu_2 > $cpu && $create2==false){

    $msg.='Start creating new container for 2<br/>';

    $create2 = true;

    $timer2 = 0;

    $cont2 = ceil($cont2 * $current_cpu_2 / $cpu) ; // autoscaling logic

    //$current_cpu_2 -= $totReq * rand(0.5,1);

  }





  // calculate time



   //if($req1 > $cont1 * $reqCont) $time1 = (1+($req1 - $cont1 * $reqCont)/10) * rand($minTime,$maxTime);

   $deviation_1 = 0;

   $deviation_2 = 0;



   if ($req1 == 0){

    $time1 = $rtt1;

  } else{

   $time1 = $rtt1 + ($req1-1) * $rtt1 + rand($minTime,$maxTime);

   $deviation_1 += ($time1 - $avg_response_time);

  }

   //if($req2 > $cont2 * $reqCont) $time2 = (1+($req2 - $cont2 * $reqCont)/10) * rand($minTime,$maxTime);

   if ($req2 == 0){

    $time2 = $rtt2;

  }else{

   $time2 = $rtt2 + ($req2-1) * $rtt1 + rand($minTime,$maxTime);

   $deviation_2 += ($time2 - $avg_response_time);

   }

  // new containers creation



   if($create1){



      $timer1++;



      if($timer1 >= $deployTime){



        $timer1 = 0;



        $create1 = false;



        //$cont1++;



        $msg.='Created container for 1<br/>';



      }}







    if($create2){



       $timer2++;



       if($timer2 >= $deployTime){



         $timer2 = 0;



         $create2 = false;



         //$cont2++;



         $msg.='Created container for 2<br/>';



       }}







  // print



   echo '<tr><td>'.$i.'</td>



             <td>'.$totReq.'</td>



             <td>'.$newOnes.'</td>



             <td>'.$outOnes.'</td>



             <td>'.$req1.'</td>



             <td>'.$time1.'</td>



             <td>'.$cont1.'</td>



             <td>'.$req2.'</td>



             <td>'.$time2.'</td>



             <td>'.$cont2.'</td>



             <td>'.$current_cpu_1.'</td>



             <td>'.$current_cpu_2.'</td>



             <td>'.$req1.'</td>



             <td>'.$req2.'</td>



        </tr>';



        if($msg != '') echo '<tr><td colspan="100%">'.$msg.'</td></tr>';



        $msg = '';



   // save values

   $expected_total_response_time = $avg_response_time * $samples;

   $total_deviation = $deviation_1 + $deviation_2;



   array_push($series1,$time1);



   array_push($series2,$time2);



   array_push($reqSeries1,$req1);



   array_push($reqSeries2,$req2);



   array_push($contSeries1,$cont1);



   array_push($contSeries2,$cont2);



   array_push($cpuSeries1,$current_cpu_1);



   array_push($cpuSeries2,$current_cpu_2);



}



    //generate csv



    $csvContent1 = '';



    $csvContent2 = '';



    $sseries = array();



    for($i=0;$i<$samples;$i++){



      $csvContent1.=$i.', '.$series1[$i].', '.$reqSeries1[$i].', '.$contSeries1[$i].', '.$cpuSeries1[$i].','.$series2[$i].', '.$reqSeries2[$i].', '.$contSeries2[$i].', '.$cpuSeries2[$i]."\n";



      $csvContent2.=$i.', '.$contSeries1[$i].', '.$cpuSeries1[$i].', '.$contSeries2[$i].', '.$cpuSeries2[$i]."\n";



      array_push($sseries,$i);

    }



    //save csv



    $myfile = fopen("data1.csv", "w") or die("Unable to open file data.csv!");



    fwrite($myfile, $csvContent1);



    fclose($myfile);



    $myfile = fopen("data2.csv", "w") or die("Unable to open file data.csv!");



    fwrite($myfile, $csvContent2);



    fclose($myfile);







?>



</table>





<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js" type="text/javascript"></script>











<script>



var ctx = document.getElementById('myChart1').getContext('2d');

var myChart = new Chart(ctx, {

    type: 'line',

    data: {

        labels: <?=json_encode($sseries)?>,

        datasets: [{

            label: 'Server 1',

            data: <?=json_encode($series1)?>,

            borderWidth: 1,

            borderColor: 'red'

        },

        {

            label: 'Server 2',

            data: <?=json_encode($series2)?>,

            borderWidth: 1,

            borderColor: 'blue'

        }]

    },

    options: {

        scales: {

            yAxes: [{

              scaleLabel: {

                display: true,

                labelString: 'Response time [ms]'

              },

                ticks: {

                    beginAtZero: true

                }

            }],

            xAxes: [{

              scaleLabel: {

                display: true,

                labelString: 'Requests'

              }

            }]

        }

    }

});





var ctx = document.getElementById('myChart2').getContext('2d');

var myChart = new Chart(ctx, {

    type: 'line',

    data: {

        labels: <?=json_encode($sseries)?>,

        datasets: [{

            label: 'Server 1',

            data: <?=json_encode($cpuSeries1)?>,

            borderWidth: 1,

            borderColor: 'red'

        },

        {

            label: 'Server 2',

            data: <?=json_encode($cpuSeries2)?>,

            borderWidth: 1,

            borderColor: 'blue'

        }]

    },

    options: {

        scales: {

            yAxes: [{

              scaleLabel: {

                display: true,

                labelString: 'CPU utilization [%]'

              },

                ticks: {

                    beginAtZero: true

                }

            }],

            xAxes: [{

              scaleLabel: {

                display: true,

                labelString: 'Requests'

              }

            }]

        }

    }

});

</script>

</body>

