<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
    
    <?php 
        //function to convert summoner name and get their summoner id
        function convert_summoner_name($summoner, $server, $key){
            $summoner_encoded = rawurlencode($summoner);
            $summoner_lower = strtolower($summoner_encoded);  
            $curl = curl_init('https://' . $server . '.api.pvp.net/api/lol/' . $server . '/v1.4/summoner/by-name/' .                 $summoner . '?api_key=' . $key);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($curl);
            curl_close($curl);
            return $result;
        }

        //function to convert summoner name into correct format
        function summoner_info_array_name($summoner) {
            $summoner_lower = mb_strtolower($summoner, 'UTF-8');
            $summoner_nospaces = str_replace(' ', '', $summoner_lower);
            return $summoner_nospaces;
        }

        //function to get a summoner's games
        function get_summoner_games($summoner_id, $server, $key) {
            $curl = curl_init('https://' . $server . '.api.pvp.net/api/lol/' . $server . '/v1.3/game/by-summoner/' .                 $summoner_id . '/recent?api_key=' . $key);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($curl);
            curl_close($curl);
            return $result;
        }
       
    
        //function to get champion id, level, goldEarned, numberOfDeaths, minionsKilled, championsKilled, assists
        function recent_games_data($summoner_game_info_array, $index, $server, $key){
            $championId = $summoner_game_info_array['games'][$index]['championId'];
            $championName = get_champion_name($key, $championId, $server);
            $numDeaths = 0;
            $minionsKilled = 0;
            $championsKilled = 0;
            $assists = 0;
            if(array_key_exists('numDeaths', $summoner_game_info_array['games'][$index]['stats'])) {
                $numDeaths = $summoner_game_info_array['games'][$index]['stats']['numDeaths'];
            }else{
                $numDeaths = 0;   
            }
            
            if(array_key_exists('minionsKilled', $summoner_game_info_array['games'][$index]['stats'])){
                $minionsKilled = $summoner_game_info_array['games'][$index]['stats']['minionsKilled'];               
            }else{
                $minionsKilled = 0;   
            }
            
            if(array_key_exists('championsKilled', $summoner_game_info_array['games'][$index]['stats'])){
                $championsKilled = $summoner_game_info_array['games'][$index]['stats']['championsKilled'];              
            }else{
                $championsKilled = 0;   
            }
            
            if(array_key_exists('assists', $summoner_game_info_array['games'][$index]['stats'])){
                $assists = $summoner_game_info_array['games'][$index]['stats']['assists'];               
            }else{
                $assists = 0;   
            }
            
            $summoner_parsed_game_data = [
                "championName" => $championName,
                "level" => $summoner_game_info_array['games'][$index]['stats']['level'],
                "goldEarned" => $summoner_game_info_array['games'][$index]['stats']['goldEarned'],
                "numDeaths" => $numDeaths,
                "minionsKilled" => $minionsKilled,
                "championsKilled" => $championsKilled,
                "assists" => $assists,
            ];
            return $summoner_parsed_game_data;
        }

         //function to get champion name
        function get_champion_name($key, $championId, $server){
            $curl = curl_init('https://global.api.pvp.net/api/lol/static-data/'.$server.'/v1.2/champion/' .                 $championId . '?api_key=' . $key);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($curl);
            curl_close($curl);
            $result_array = json_decode($result, true);
            $championName = $result_array['name'];
            return $championName;
        }
    ?>
    
    <?php 
        //declaration of variables
        $key = "20532a40-5c51-4e07-bcae-f32b7daeea26";
        $summoner = $_POST["name"];
        $server = $_POST["regionDropDown"];
        $totalRecentGames = 10;
        $totalKills = 0;
        $totalDeaths = 0;
        $totalAssists = 0;

        //get the summoner id
        $summoner_info = convert_summoner_name($summoner, $server, $key);
        $summoner_info_array = json_decode($summoner_info, true);
        $summoner_info_array_name = summoner_info_array_name($summoner);

        
        //check if summoner exists
        if(array_key_exists('status', $summoner_info_array)){
            if(array_key_exists('status_code', $summoner_info_array['status'])) {
                $error_code = $summoner_info_array['status']['status_code'];               
            }else{
                $error_code = 0;   
            }
        }else{
            $error_code = 0;
        }

        //if summoner doesn't exist print invalid out else go ahead and get their details
        if($error_code == 404) {
            echo 'Invalid summoner name for this region.';
        }else if($error_code == 0){
            echo 'Welcome ' . $summoner . '<br>'; 
            echo 'Your region is: ' . $server . '<br>';
            $summoner_id = $summoner_info_array[$summoner_info_array_name]['id'];
            echo 'Your summoner id is: ' . $summoner_id . '<br>';
            //use summoner id to retrieve game details
            $summoner_game_info = get_summoner_games($summoner_id, $server, $key);
            $summoner_game_info_array = json_decode($summoner_game_info, true);
            
            for ($index = 0; $index < $totalRecentGames; $index++){
                $summoner_game_parsed_data = recent_games_data($summoner_game_info_array, $index, $server, $key);
                echo 'Game '. $index . '<br>';
                echo 'Champion Name: ' .$summoner_game_parsed_data['championName'] . '<br>';
                echo 'Level: ' .$summoner_game_parsed_data['level'] . '<br>';
                echo 'Gold Earned: ' .$summoner_game_parsed_data['goldEarned'] . '<br>';
                echo 'Number of Deaths: ' .$summoner_game_parsed_data['numDeaths'] . '<br>';
                echo 'Minions Killed: ' .$summoner_game_parsed_data['minionsKilled'] . '<br>';
                echo 'Champions Killed: ' .$summoner_game_parsed_data['championsKilled'] . '<br>';
                echo 'Assists: ' .$summoner_game_parsed_data['assists'] . '<br><br>';
                
                $totalKills = $summoner_game_parsed_data['championsKilled'] + $totalKills;
                $totalDeaths = $summoner_game_parsed_data['numDeaths'] + $totalDeaths;
                $totalAssists = $summoner_game_parsed_data['assists'] + $totalAssists;     
                
            }
            //run the script for creating a google table
            echo '
            <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
            <script type="text/javascript">

              // Load the Visualization API and the corechart package.
              google.charts.load("current", {"packages":["corechart"]});

              // Set a callback to run when the Google Visualization API is loaded.
              google.charts.setOnLoadCallback(drawChart);

              // Callback that creates and populates a data table,
              // instantiates the pie chart, passes in the data and
              // draws it.
              function drawChart() {

                // Create the data table.
                var data = new google.visualization.DataTable();
                data.addColumn("string", "Type");
                data.addColumn("number", "Stat");
                data.addRows([
                  ["Kills", '.$totalKills.'],
                  ["Deaths", '.$totalDeaths.'],
                  ["Assists", '.$totalAssists.']
                ]);

                // Set chart options
                var options = {"title":"Total kills, deaths and assists",
                               "width":400,
                               "height":300};

                // Instantiate and draw our chart, passing in some options.
                var chart = new google.visualization.PieChart(document.getElementById("chart_div"));
                chart.draw(data, options);
              }
            </script>';

        }
    

        
    ?>
    <!--Div that will hold the pie chart-->
    <div id="chart_div"></div>

</body>
</html>