<?php

Class Parameters{
    const FILE_NAME = 'produk.txt';
    const COLUMNS = ['item', 'price'];
    const POPULATION_SIZE = 10;
    const BUDGET = 280000;
    const STOPING_VALUE = 10000;
    const CROSSOVERRATE = 0.8;//digunakan untuk menyaring individu yang terpilih menjadi individu parent
}


class Catalogue
{
    
    function createProductColumn($listOfRawProduct){
        foreach(array_keys($listOfRawProduct) as $listOfRawProductKey){
            $listOfRawProduct[Parameters::COLUMNS[$listOfRawProductKey]] = $listOfRawProduct[$listOfRawProductKey];
            unset($listOfRawProduct[$listOfRawProductKey]);
        }
        return $listOfRawProduct;
    }

    function product(){
        $collectionOfListProduct = [];

        $raw_data = file(Parameters::FILE_NAME);
        foreach ($raw_data as $listOfRawProduct){
            $collectionOfListProduct[] = $this -> createProductColumn(explode(",",$listOfRawProduct));
        }

        // foreach($collectionOfListProduct as $listOfRawProduct){
        //     print_r($listOfRawProduct);
        //     echo '<br>';
        // }

        return $collectionOfListProduct;

    }
}

Class individu{
    function countNumberOfGen(){
        $catalogue = new Catalogue;
        return count($catalogue -> product());
    }

    function createRandomIndividu(){
        for ($i=0;$i<= $this->countNumberOfGen() - 1;$i++){
            $ret[] = rand(0,1);
        }
        return $ret;
        
    }
}

class Population{
  
    function createRandomPopulation(){
        $individu = new individu;
        for($i = 0;$i <= Parameters::POPULATION_SIZE - 1 ; $i++){
           $ret[] =  $individu -> createRandomIndividu();
        }
        return $ret;
        
    }
}

class Fitness{
    function selectingItem($individu){
        $catalogue = new Catalogue;
        foreach($individu as $individuKey => $binaryGen){
            if($binaryGen === 1){
                $ret[] = [
                    'selectedKey' => $individuKey,
                    'selectedPrice' => $catalogue -> product()[$individuKey]['price']
                ]; 
            }
            
        }
        return $ret;
    }

    function calculateFitnessValue($individu){
        return array_sum(array_column($this -> selectingItem($individu),'selectedPrice'));
       
    }

    function countSelectedItem($individu){
        return count($this->selectingItem($individu));
    }

    function searchBestIndividu($fits,$maxItem,$numberOfIndividuMaxItem){
        if($numberOfIndividuMaxItem === 1){
            $index = array_search($maxItem, array_column($fits,'numberOfSelectedItem'));
            return $fits[$index];
            echo'<br>';
        }
        else{ 
            foreach($fits as $key => $val){
                if($val['numberOfSelectedItem'] === $maxItem){
                    echo $key.' '.$val['fitnessValue'].'<br>';
                    $ret[] =[
                        'individuKey' => $key,
                        'fitnessValue' => $val['fitnessValue']

                    ];
                }
            }
            if(count(array_unique(array_column($ret, 'fitnessValue'))) === 1){
                $index = rand(0, count($ret) - 1);
            }
            else{
                $max = max(array_column($ret,'fitnessValue'));
                $index = array_search($max,array_column($ret,'fitnessValue'));
            }
            echo '<br>Hasil: ';
            // print_r($ret[$index]);
            return $ret[$index]; 
        }
    }

    function isFound($fits){
        $countedMaxItem = array_count_values(array_column($fits,'numberOfSelectedItem'));
        print_r($countedMaxItem);
        echo '<br>';
        $maxItem = max(array_keys($countedMaxItem));
        echo $maxItem;
        echo '<br>';
        echo $countedMaxItem[$maxItem];
        echo '<br>';
        $numberOfIndividuMaxItem = $countedMaxItem[$maxItem];

        $bestFitnessValue = $this -> searchBestIndividu($fits,$maxItem,$numberOfIndividuMaxItem)['fitnessValue'];
        //print_r($bestFitnessValue)['fitnessValue'];
        echo '<br>Best fitness value: '.$bestFitnessValue;
 
 
        $residual = Parameters::BUDGET - $bestFitnessValue;
        echo 'Residual: '. $residual;

        if($residual <= Parameters::STOPING_VALUE && $residual > 0){
            return True;
        }
    }

    function isFit($fitnessValue){
        if($fitnessValue <= Parameters::BUDGET){
            return True;
        }
    }

    function fitnessEvaluation($population){
        $catalogue = new Catalogue;
        foreach($population as $listOfindividuKey => $listOfIndividu){
            echo 'Individu-'. $listOfindividuKey. '<br>';
            foreach ($listOfIndividu as $individuKey => $binaryGen){
                echo $binaryGen.'&nbsp;&nbsp';
                print_r($catalogue -> product()[$individuKey]);
                echo '<br>';
            }
            $fitnessValue = $this->calculateFitnessValue($listOfIndividu); 
            $numberOfSelectingItem = $this -> countSelectedItem($listOfIndividu);
            echo 'Max Item: '.$numberOfSelectingItem;
            echo  ' Fitness Value :'. $fitnessValue;
            if($this -> isFit($fitnessValue)){
                echo '(Fit)';
                $fits[] = [
                    'selectedIndividuKey' => $listOfindividuKey,
                    'numberOfSelectedItem' => $numberOfSelectingItem,
                    'fitnessValue' => $fitnessValue
                ];
                print_r($fits); 
            }
            else{
                echo '(Not Fit)';
            }
           
            echo '<p>';
        }
        if($this -> isFound($fits)){
            echo ' Found';
        }
        else{
            echo'>> next generation';
        }
       
    }
}
$parameters = [
    'file_name' => 'produk.txt',
    'columns' => ['item', 'price'],
    'population_size' => 10
];


class Crossover{
    //mengawin silangkan antara 2 kromosom orang tua / 2 individu  yaitu 2 parent  ayah ibu 
    //sehingga kromosom pada individu anak yang memiliki sebagian kromosom dari orang tua atau disebut offspring
    // jadi pada individu anak terdapat sebagian gen-gen ayah dan sebagian gen-gen ibu yang membentuk individu baru  
    public $population;
    
    function __construct($population){
        $this -> population = $population;//memanggil variabel population
    }

    function randomZerotoOne(){
        return (float) rand() / (float) getrandmax();//menentukan nilai acak antara 0 sampai 1
    }
 
    function generateCrossover(){
        //digunakan untuk membuat crossover
        for($i = 0;$i<= Parameters::POPULATION_SIZE-1;$i++){
            $randomZerotoOne = $this -> randomZerotoOne();
            if($randomZerotoOne < Parameters::CROSSOVERRATE){ 
                // jika nilai Randomtozero pada setiap individu kurang dari 0.8 maka akan menjadi parent
                $parents[$i] = $randomZerotoOne;

            }
        }
        foreach (array_keys($parents) as $key) {
            //digunakan untuk mendapatkan key pada parent
            foreach (array_keys($parents) as $subkey) {
                if($key !== $subkey){
                    $ret[] = [$key,$subkey];
                }
                
            }
            array_shift($parents);
        }
        return $ret;//mereturn nilai ret 
    }

    function offspring($parents1,$parents2,$cutPointIndex,$offspring){
        //membagi kromosom parent1 dan parent2 yang nanti akan digabung menjadi individu baru 
        $lengthOfgen = new Individu;

        //indivudu baru 1
        if($offspring === 1){
            //proses memisah kromosom pada parent yang akan dimasukan ke kromosom indivdu baru
            for ($i=0;$i<=$lengthOfgen->countNumberOfGen()-1;$i++){
                //kromosom sebelah kiri parent 1 pada batas cut index akan dimasukan ke individu baru
                if($i <= $cutPointIndex){
                    $ret[] = $parents1[$i];
                }
                // dan kromosom sebelah kanan parent 2 pada batas cut index akan dimasukan ke individu baru
                if($i > $cutPointIndex){
                    $ret[] = $parents2[$i];
                }
            }
            //maka individu baru telah dibuat
            
        }

        //indivudu baru 2
        if($offspring === 2){
            for ($i=0;$i<=$lengthOfgen->countNumberOfGen()-1;$i++){
                 //kromosom sebelah kiri parent 2 pada batas cut index akan dimasukan ke individu baru
                if($i <= $cutPointIndex){
                    $ret[] = $parents2[$i];
                }
                if($i > $cutPointIndex){
                // dan kromosom sebelah kanan parent 1 pada batas cut index akan dimasukan ke individu baru
                    $ret[] = $parents1[$i];
                }
            }
            //maka individu baru telah dibuat
           
        }
        return $ret;
    }

    function cutPointRandom(){
        $lengthOfgen = new Individu; 
        return rand(0,$lengthOfgen->countNumberOfGen()-1);
        //memnentukan batas perpotongan kromosom pada parent yang nanti digabung menjadi individu baru
    }

    function crossover(){
        $cutPointIndex = $this->cutPointRandom();//memnentukan batas perpotongan
        echo"<br> Cut Point Index: ";
        echo $cutPointIndex;
        foreach($this->generateCrossover() as $listCrossover){
            //proses mengambil parent yang telah terpilih
            $parents1 = $this -> population[$listCrossover[0]];
            $parents2 = $this -> population[$listCrossover[1]];
            echo"<br><br>";
            echo"Parents: <br>";
            foreach($parents1 as $gen){
                echo $gen;//mengoutputkan parent 1
            }
            echo'><';
            foreach($parents2 as $gen){
                echo $gen;//mengoutputkan parent 2
            }
            echo"<br>";
            echo"Offspring Index: <br >";
            //proses crossover yang nanti akan menghasilkan 2 individu baru
            $offspring1 = $this->offspring($parents1,$parents2,$cutPointIndex,1);
            $offspring2 = $this->offspring($parents1,$parents2,$cutPointIndex,2);
            foreach($offspring1 as $gen){
                echo $gen;//hasil crossover individu baru ke 1
            }
            echo'><';
            foreach($offspring2  as $gen){

                echo $gen;//hasil crossover individu baru ke 2
            }
        }
        
    } 

}



// $katalog = new Catalogue;
// // print_r($katalog -> product($parameters));
// $katalog -> product($parameters);

$initialPopulation = new Population;
$population = $initialPopulation -> createRandomPopulation();

$fitness = new Fitness;
$fitness -> fitnessEvaluation($population);

$crossover = new Crossover($population);//membuat objek baru pada class Crossover 
$crossover->crossover();

// $individu = new individu;
// print_r($individu -> createRandomIndividu());