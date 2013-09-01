<?php
/**
 * Super Class
 *
 * @package        Input
 * @subpackage    Subpackage
 * @category    Category
 * @author        hoshi~
 * @link        https://github.com/awkwardusername
 * @date        8/28/13 | 3:22 AM
 */

class Input extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->helper(array('url', 'inflector'));

        $this->load->model('run_templates_data_model');
        $this->load->model('weather_data_model');
        $this->load->model('run_cache_model');
    }

    public function index()
    {
        $data['title'] = 'Simulation';

        $data['template'] = $this->run_templates_data_model->get_template();
        $data['weather_years'] = $this->weather_data_model->get_country_year_list();
        $data['years'] = $this->weather_data_model->get_years();
        $data['first_year'] = $this->weather_data_model->get_first_year();
        $data['sites'] = $this->weather_data_model->get_countries();

        $this->load->view('templates/header', $data);
        $this->load->view('templates/content-start', $data);
        $this->load->view('pages/input', $data);
        $this->load->view('templates/content-end', $data);
        $this->load->view('templates/footer', $data);
    }

    public function retrieve_template($variety, $file)
    {
        $template = $this->run_templates_data_model->get_template();
        header("Content-Type: text/plain");
        echo $template[$variety][$file];
    }

    public function save_template()
    {
        $this->run_templates_data_model->add();
    }

    /*
     * @function         simulate_basic
     * @description      runs a simulation using basic input
     * @params           $site          sets the location of weather data, maps to CNTR and ISTN at *.exp file
     *                   $year          sets the year of weather data, maps to IYEAR = EMYR at *.exp file
     *                   $variety       fetches the template for getting the *.exp and *.crp data
     *                   $dateofsowing  sets the day of year of the start day of simulation, maps to STTIME at *.exp
     *                   $seeding       sets method of seeding, maps to ESTAB at *.exp
     *                   $sdbdur        sets seedbed duration on ESTAB = 'TRANSPLANT'.
     *
     * eg. <?= base_url ?>input/simulate_basic/phil/1991/long_term/0?/d/45
     */
    public function simulate_basic($site, $year, $variety, $dateofsowing, $seeding, $sdbdur)
    {
        $template_data = $this->run_templates_data_model->get_template($variety);
        $weather_data = $this->weather_data_model->get_weather_from_country_till_selected_years($site, $year);

        $control_dat = $template_data['control_dat'];
        $experiment_data_dat = $template_data['experiment_data_dat'];
        $crop_data_dat = $template_data['crop_data_dat'];

        // echo $control_dat;

        $control_dat = $this->modify_control_dat($control_dat, $template_data['file_prefix']);
        $experiment_data_dat = $this->modify_experiment_data_dat($experiment_data_dat, $site, $year, $dateofsowing, $seeding, $sdbdur);

        write_file('./temp/control.dat', $control_dat);
        write_file('./temp/' . $template_data['file_prefix'] . '.crp', $crop_data_dat);
        write_file('./temp/reruns.dat', $experiment_data_dat['reruns']);
        write_file('./temp/' . $template_data['file_prefix'] . '.exp', $experiment_data_dat['experiment_data']);

        foreach ($weather_data as $weather) {
            write_file('./temp/' . $weather['country_code'] . $weather['station_code'] . '.' . substr($weather['year'], 1, 3), $weather['data']);
        }

        exec('./temp/oryza2000 control.dat', $exec_output = array());

        header("Content-Type: text/plain");

        echo sha1($site . $year . $variety . $dateofsowing . $seeding . $sdbdur);

        //print_r($exec_output);
    }

    private function modify_control_dat($control_dat, $file_prefix)
    {
        $control_dat = preg_replace("/(FILEIT)(\\s*)(=)(\\s*)(\\'.*?\\')/", 'FILEIT = \'' . $file_prefix . '.exp\'', $control_dat, 1);
        $control_dat = preg_replace("/(FILEI1)(\\s*)(=)(\\s*)(\\'.*?\\')/", 'FILEI1 = \'' . $file_prefix . '.crp\'', $control_dat, 1);

        return $control_dat;
    }

    private function modify_experiment_data_dat($experiment_data_dat, $site, $year, $dateofsowing, $seeding, $sdbdur)
    {
        $first_year = $this->weather_data_model->get_first_year();
        $rerun_dat = '';

        if ($year >= $first_year['year']) {
            if ($dateofsowing > 0) {
                $count = 1;
                for ($i = $first_year['year']; $i <= $year; $i++) {
                    $day = date('L', strtotime("$i-1-1")) ? 366 : 365;
                    for ($j = 1; $j <= $day; $j++) {
                        $rerun_dat = $rerun_dat . "* rerun # {$count}\r\nIYEAR = {$i}\r\nEMYR = {$i} \r\n";
                        $rerun_dat = $rerun_dat . "EMD = {$j}\r\n";
                    }
                    $count++;
                }
            }
        } elseif ($year === $first_year['year']) {
            $experiment_data_dat = preg_replace("/(IYEAR)(\\s*)(=)(\\s+)((?:(?:[1]{1}\\d{1}\\d{1}\\d{1})|(?:[2]{1}\\d{3})))(?![\\d])/", 'IYEAR = ' . $year, $experiment_data_dat);
            $experiment_data_dat = preg_replace("/(EMYR)(\\s*)(=)(\\s+)((?:(?:[1]{1}\\d{1}\\d{1}\\d{1})|(?:[2]{1}\\d{3})))(?![\\d])/", 'EMYR = ' . $year, $experiment_data_dat);
        } else {
            echo 'wrong' . $year . $first_year['year'];
        }
        $station_code = $this->weather_data_model->get_station_code($site);

        $experiment_data_dat = preg_replace("/(CNTR)(\\s*)(=)(\\s*)(\\'.*?\\')/", 'CNTR = \'' . $station_code['country_code'] . '\'', $experiment_data_dat);
        $experiment_data_dat = preg_replace("/(ISTN)(\\s*)(=)(\\s*)(\\d+)/", 'ISTN = ' . $station_code['station_code'], $experiment_data_dat);

        if ($dateofsowing !== 0)
            $experiment_data_dat = preg_replace("/(STTIME)(\\s*)(=)(\\s*)(\\d+)/", 'STTIME = ' . $dateofsowing, $experiment_data_dat);

        if ($seeding === 't') {
            $experiment_data_dat = preg_replace("/(ESTAB)\\s*(=)\\s*(\\'.*?\\')/", 'ESTAB = \'TRANSPLANT\'', $experiment_data_dat);
            $experiment_data_dat = preg_replace("/(SBDUR)(\\s*)(=)(\\s*)(\\d+)/", 'SDBUR = ' . $sdbdur, $experiment_data_dat);
        } elseif ($seeding === 'd')
            $experiment_data_dat = preg_replace("/(ESTAB)\\s*=\\s*(\\'.*?\\')/", 'ESTAB = \'DIRECT-SEED\'', $experiment_data_dat); else
            show_404();

        return array('reruns' => $rerun_dat, 'experiment_data' => $experiment_data_dat);
    }

    public function parse_weather_data($country_code, $year)
    {
        $weather_data = $this->weather_data_model->get_weather_data($country_code, $year);

        $re1 = '(\\s+)(\\d+)(\\s+)((?:(?:[1]{1}\\d{1}\\d{1}\\d{1})|(?:[2]{1}\\d{3})))(?![\\d])(\\s+)(\\d+)(\\s+)(\\d+)(\\s+)([+-]?\\d*\\.\\d+)(?![-+0-9\\.])(\\s+)([+-]?\\d*\\.\\d+)(?![-+0-9\\.])(\\s+)([+-]?\\d*\\.\\d+)(?![-+0-9\\.])(\\s+)([+-]?\\d*\\.\\d+)(?![-+0-9\\.])(\\s+)([+-]?\\d*\\.\\d+)(?![-+0-9\\.])'; # Float 5

        $weather_data_array = explode("\n", $weather_data['data']);

        $s = 0;

        foreach ($weather_data_array as $data) {
            if (preg_match_all("/" . $re1 . "/is", $data, $matches)) {

                $int1 = $matches[2][0];
                $year1 = $matches[4][0];
                $int2 = $matches[6][0];
                $int3 = $matches[8][0];
                $float1 = $matches[10][0];
                $float2 = $matches[12][0];
                $float3 = $matches[14][0];
                $float4 = $matches[16][0];
                $float5 = $matches[18][0];

                $graph_me[$s++] = array($int1, $year1, $int2, $int3, $float1, $float2, $float3, $float4, $float5);
            }
        }

        print_r($graph_me);
    }

    public function parse_res_dat($hash)
    {
        $run_cache = $this->run_cache_model->search_run_cache($hash);

        $res_dat_exploded = explode("\n", $run_cache['res_dat']);

        //print_r($res_dat_exploded);

        //preg_match_all("/([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)/is", $boom, $matches)

        // test = ([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)
        // test2 =([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)
        // test3 = ([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)?
        // asdads = ([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)

        $re1 = '(\\s+)'; # White Space 1
        $re2 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 1
        $re3 = '(\\s+)'; # White Space 2
        $re4 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 2
        $re5 = '(\\s+)'; # White Space 3
        $re6 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 3
        $re10 = '(\\s+)'; # White Space 4
        $re11 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 4
        $re12 = '(\\s+)'; # White Space 5
        $re13 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 5
        $re14 = '(\\s+)'; # White Space 6
        $re15 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 6
        $re18 = '(\\s+)'; # White Space 7
        $re19 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 7
        $re22 = '(\\s+)'; # White Space 8
        $re23 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 8
        $re24 = '(\\s+)'; # White Space 9
        $re25 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 9
        $re26 = '(\\s+)'; # White Space 10
        $re27 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 10
        $re30 = '(\\s+)'; # White Space 11
        $re31 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 11
        $re32 = '(\\s+)'; # White Space 12
        $re33 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 12
        $re34 = '(\\s+)'; # White Space 13
        $re35 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 13
        $re36 = '(\\s+)'; # White Space 14
        $re37 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 14
        $re38 = '(\\s+)'; # White Space 15
        $re39 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 15
        $re40 = '(\\s+)'; # White Space 16
        $re41 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 16
        $re42 = '(\\s+)'; # White Space 17
        $re43 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 17
        $re46 = '(\\s+)'; # White Space 18
        $re47 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 18
        $re48 = '(\\s+)'; # White Space 19
        $re49 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 19
        $re50 = '(\\s+)'; # White Space 20
        $re51 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 20
        $re52 = '(\\s+)'; # White Space 21
        $re53 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 21
        $re56 = '(\\s+)'; # White Space 22
        $re57 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 22
        $re58 = '(\\s+)'; # White Space 23
        $re59 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 23
        $re60 = '(\\s+)'; # White Space 24
        $re61 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 24
        $re64 = '(\\s+)'; # White Space 25
        $re65 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 25
        $re66 = '(\\s+)'; # White Space 26
        $re67 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 26
        $re68 = '(\\s+)'; # White Space 27
        $re69 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 27
        $re70 = '(\\s+)'; # White Space 28
        $re71 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 28
        $re72 = '(\\s+)'; # White Space 29
        $re73 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 29
        $re74 = '(\\s+)'; # White Space 30
        $re75 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 30
        $re76 = '(\\s+)'; # White Space 31
        $re77 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 31
        $re78 = '(\\s+)'; # White Space 32
        $re79 = '([+-]?\\d*\\.\\d+)(?![-+0-9\\.])((E)([-+]\\d+)?)|(-)'; # Float 32

        $s = 0;

        foreach ($res_dat_exploded as $boom) {
            if (preg_match_all("/" . $re1 . $re2 . $re3 . $re4 . $re5 . $re6 . $re10 . $re11 . $re12 . $re13 . $re14 . $re15 . $re18 . $re19 . $re22 . $re23 . $re24 . $re25 . $re26 . $re27 . $re30 . $re31 . $re32 . $re33 . $re34 . $re35 . $re36 . $re37 . $re38 . $re39 . $re40 . $re41 . $re42 . $re43 . $re46 . $re47 . $re48 . $re49 . $re50 . $re51 . $re52 . $re53 . $re56 . $re57 . $re58 . $re59 . $re60 . $re61 . $re64 . $re65 . $re66 . $re67 . $re68 . $re69 . $re70 . $re71 . $re72 . $re73 . $re74 . $re75 . $re76 . $re77 . $re78 . $re79 . "/is", $boom, $matches, PREG_SET_ORDER )) {
                $s++;

                /*foreach ($matches as $asduff) {
                    print_r( $asduff );
                }*/


                $float1 = $matches[0][0];
                $float2 = $matches[1][0];
                $float3 = $matches[2][0];
                $float4 = $matches[3][0];
                $float5 = $matches[4][0];
                $float6 = $matches[5][0];
                $float7 = $matches[6][0];
                $float8 = $matches[7][0];
                $float9 = $matches[8][0];
                $float10 = $matches[9][0];
                $float11 = $matches[10][0];
                $float12 = $matches[11][0];
                $float13 = $matches[12][0];
                $float14 = $matches[13][0];
                $float15 = $matches[14][0];
                $float16 = $matches[15][0];
                $float17 = $matches[16][0];
                $float18 = $matches[17][0];
                $float19 = $matches[18][0];
                $float20 = $matches[19][0];
                $float21 = $matches[20][0];
                $float22 = $matches[21][0];
                $float23 = $matches[22][0];
                $float24 = $matches[23][0];
                $float25 = $matches[24][0];
                $float26 = $matches[25][0];
                $float27 = $matches[26][0];
                $float28 = $matches[27][0];
                $float29 = $matches[28][0];
                $float30 = $matches[29][0];
                $float31 = $matches[30][0];
                $float32 = $matches[31][0];


                $graph_me[$s++] = array($float1, $float2, $float3, $float4, $float5, $float6, $float7, $float8, $float9, $float10, $float11, $float12, $float13, $float14, $float15, $float16, $float17, $float18, $float19, $float20, $float21, $float22, $float23, $float24, $float25, $float26, $float27, $float28, $float29, $float30, $float31, $float32);

            }
        }

        echo $s;
        /*
        //print_r($graph_me);
        for($i = 0; $i < 100 ; $i++) {
            print_r($matches[$i][0]);
        }

        */

        print_r($graph_me);

    }

    public function parse_op_dat($hash) {
        $run_cache = $this->run_cache_model->search_run_cache($hash);

        $op_dat_exploded = explode("\n", $run_cache['op_dat']);

        $re1='(\\s+)';	# White Space 1
        $re2='((?:(?:[0-2]?\\d{1})|(?:[3][01]{1})))(?![\\d])';	# Day 1
        $re3='(\\s+)';	# White Space 2
        $re4='([+-]?\\d*\\.\\d+)(?![-+0-9\\.])';	# Float 1
        $re5='(\\s+)';	# White Space 3
        $re6='([+-]?\\d*\\.\\d+)(?![-+0-9\\.])';	# Float 2
        $re7='(\\s+)';	# White Space 4
        $re8='([+-]?\\d*\\.\\d+)(?![-+0-9\\.])';	# Float 3
        $re9='(\\s+)';	# White Space 5
        $re10='(\\d+)';	# Integer Number 1
        $re11='.*?';	# Non-greedy match on filler
        $re12='(\\s+)';	# White Space 6
        $re13='(\\d+)';	# Integer Number 2
        $re14='.*?';	# Non-greedy match on filler
        $re15='(\\s+)';	# White Space 7
        $re16='(\\d+)';	# Integer Number 3
        $re17='.*?';	# Non-greedy match on filler
        $re18='(\\s+)';	# White Space 8
        $re19='([+-]?\\d*\\.\\d+)(?![-+0-9\\.])';	# Float 4
        $re20='(\\s+)';	# White Space 9
        $re21='([+-]?\\d*\\.\\d+)(?![-+0-9\\.])';	# Float 5
        $re22='(\\s+)';	# White Space 10
        $re23='([+-]?\\d*\\.\\d+)(?![-+0-9\\.])';	# Float 6
        $re24='(\\s+)';	# White Space 11
        $re25='([+-]?\\d*\\.\\d+)(?![-+0-9\\.])';	# Float 7
        $re26='(\\s+)';	# White Space 12
        $re27='([+-]?\\d*\\.\\d+)(?![-+0-9\\.])';	# Float 8
        $re28='(\\s+)';	# White Space 13
        $re29='([+-]?\\d*\\.\\d+)(?![-+0-9\\.])';	# Float 9
        $re30='(\\s+)';	# White Space 14
        $re31='([+-]?\\d*\\.\\d+)(?![-+0-9\\.])';	# Float 10

        $s = 0;
        foreach ($op_dat_exploded as $asduff) {
            if (preg_match_all ("/".$re1.$re2.$re3.$re4.$re5.$re6.$re7.$re8.$re9.$re10.$re11.$re12.$re13.$re14.$re15.$re16.$re17.$re18.$re19.$re20.$re21.$re22.$re23.$re24.$re25.$re26.$re27.$re28.$re29.$re30.$re31."/is", $asduff, $matches))
            {
                $day1=$matches[2][0];
                $float1=$matches[4][0];
                $float2=$matches[6][0];
                $float3=$matches[8][0];
                $int1=$matches[10][0];
                $int2=$matches[12][0];
                $int3=$matches[14][0];
                $float4=$matches[16][0];
                $float5=$matches[18][0];
                $float6=$matches[20][0];
                $float7=$matches[22][0];
                $float8=$matches[24][0];
                $float9=$matches[26][0];
                $float10=$matches[28][0];

                $get_op_dat[$s++] = array($day1, $float1, $float2, $float3, $int1, $int2, $int3, $float4, $float2, $float5, $float6, $float7, $float8, $float9, $float10);
            }
        }

        print_r($get_op_dat);
    }


}