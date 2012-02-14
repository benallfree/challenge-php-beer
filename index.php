<?php
    require('debug.php');
    $developer = 'Ben Allfree'; // Please replace with your name
    $url = 'http://st.deviantart.net/dt/exercise/' . (isset($_GET['file']) ? $_GET['file'] : 'data.csv');
    $color = isset($_GET['color']) ? $_GET['color'] : '#C00';
    $color2 = isset($_GET['color2']) ? $_GET['color2'] : '#0C0';
    
    
    abstract class Reader {
        public function __construct($raw_data)
        {
          $this->raw_data = $raw_data;
          $this->data = $this->parse();
          $this->isReady = ($this->data !== null);
        }
        
        abstract public function parse();
        public function getData(&$data) {
          $data = $this->data;
        }
    }
    
    class ReaderFactory
    {
      function create($url)
      {
        $data = self::fetch($url);
        $types = array('JSON', 'CSV');
        foreach($types as $t)
        {
          $class = "{$t}Reader";
          $c = new $class($data);
          if($c->isReady) return $c;
        }
        return null;
      }
      
      function fetch($url)
      {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
      }    
    }
    
    class JSONReader extends Reader
    {
        public function parse()
        {
          return json_decode($this->raw_data);
        }
    }
    
    class CSVReader extends Reader {
        public function parse()
        {
          $lines = preg_split('/[\r\n]+/',$this->raw_data);
          if(count($lines)==0) return null; // can't be CSV, no lines
          $data = array();
          $col_count = 0;
          foreach($lines as $line)
          {
            if(!$line) continue;
            $line = explode(',', str_replace("\r\n", '', $line));
            if($col_count==0)
            {
              $col_count = count($line);
            } else {
              if(count($line) != $col_count)
              {
                return null; // got multiple column counts, not CSV
              }
            }
            $data[] = $line;
          }
          return $data;
        }
    }
    
    function collect($arr, $col=0)
    {
      $samples = array();
      foreach($arr as $v)
      {
        $samples[] = $v[$col];
      }
      return $samples;
    }
    
    function sd($arr, $col=0)
    {
      $samples = collect($arr, $col);
      $sample_square = array();
      $sample_count = count($samples);
      
      for ($current_sample = 0; $sample_count > $current_sample; ++$current_sample) $sample_square[$current_sample] = pow($samples[$current_sample], 2);
      
      $standard_deviation = sqrt(array_sum($sample_square) / $sample_count - pow((array_sum($samples) / $sample_count), 2));
      return $standard_deviation;
    }
    
    function mean($arr, $col=0)
    {
      $samples = collect($arr, $col);
      return array_sum($samples) / count($samples);
    }
    
    ?>
    <html>
    <head>
    <script type="text/javascript" src="http://code.jquery.com/jquery-1.4.2.js"></script>
    <script type="text/javascript" src="chart.js"></script>
    <style>
        #chart {
            position: relative;
            border-bottom: 1px #000 solid;
            border-left: 1px #000 solid;
            height: 200px;
        }
        
        #chart .value {
            background-color: #f00;
            bottom: 0;
            position: absolute;
            background: -webkit-gradient(linear, left top, left bottom, from(<?= $color ?>), to(#000));
            background: -moz-linear-gradient(top,  <?= $color ?>,  #000);
            padding: 2px;
            color: #fff;
        }
        
        #chart .abnormal {
            background-color: #0f0;
            background: -webkit-gradient(linear, left top, left bottom, from(<?= $color2 ?>), to(#000));
            background: -moz-linear-gradient(top,  <?= $color2 ?>,  #000);
        }
    </style>
    </head>
    <body onload="init();">
    
    <?
    
    $reader = ReaderFactory::create($url);
    if($reader)
    {
      echo 'Litres of beer consumed per week by ' . $developer . '<br><br>' . "\r\n";
      echo '<div id="chart">' . "\r\n";
      $reader->getData($data);
      $sd = sd($data,1);
      $mean = mean($data,1);
      foreach ($data as &$values) {
          $weeks_ago = floor((time() - $values[0]) / (60*60*24*7));
          $abnormal = '';
          if($values[1]<$mean-$sd || $values[1]>$mean+$sd) $abnormal = 'abnormal';
          echo '<div class="value '.$abnormal.'" timestamp="' . $values[0] . '" value="' . $values[1] . '" title="' . $weeks_ago . ' weeks ago: ' . $values[1] . '"></div>' . "\r\n";
      }
      echo "</div>";
    } else {
      echo "File not found or reader error: $url";
    }
    
?>
</body>
</html>
