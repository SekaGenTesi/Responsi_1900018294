<?php

Class Parameters{
    const FILE_NAME = 'produk.txt';
    const COLUMNS = ['item', 'price'];
    const POPULATION_SIZE = 30;
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
        //print_r($countedMaxItem);
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
            // foreach ($listOfIndividu as $individuKey => $binaryGen){
            //     echo $binaryGen.'&nbsp;&nbsp';
            //     //print_r($catalogue -> product()[$individuKey]);
            //     echo '<br>';
            // }
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
                //print_r($fits); 
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
        //echo"<br> Cut Point Index: ";
        // echo $cutPointIndex;
        foreach($this->generateCrossover() as $listCrossover){
            //proses mengambil parent yang telah terpilih
            $parents1 = $this -> population[$listCrossover[0]];
            $parents2 = $this -> population[$listCrossover[1]];
            // echo"<br><br>";
            // echo"Parents: <br>";
            // foreach($parents1 as $gen){
            //     echo $gen;//mengoutputkan parent 1
            // }
            // echo'><';
            // foreach($parents2 as $gen){
            //     echo $gen;//mengoutputkan parent 2
            // }
            // echo"<br>";
            // echo"Offspring Index: <br >";
            //proses crossover yang nanti akan menghasilkan 2 individu baru
            $offspring1 = $this->offspring($parents1,$parents2,$cutPointIndex,1);
            $offspring2 = $this->offspring($parents1,$parents2,$cutPointIndex,2);
            // foreach($offspring1 as $gen){
            //     echo $gen;//hasil crossover individu baru ke 1
            // }
            // echo'><';
            // foreach($offspring2  as $gen){

            //     echo $gen;//hasil crossover individu baru ke 2
            // }
            $offspring[]=$offspring1;
            $offspring[]=$offspring2;
            
        }
        return $offspring;
    } 

}

class Randomizer{
    static function getRandomIndexOfGen(){
        return rand(0,(new Individu())->countNumberOfGen()-1);//me-return nilai random dari 0 sampai jumlah gen - 1
    }

    static function getRandomIndexOfIndividu(){
        return rand(0,Parameters::POPULATION_SIZE - 1);//me-return nilai random dari 0 sampai jumlah inidividu pada populasi - 1
    }
}

class Mutation{
    
    function __construct($population){
        $this->population = $population;
    }

    function calculateMutationRate(){
        //digunakan untuk menghitung mutation rate dengan menggunakan menghitung 1 / jumlah individu
        return 1/(new Individu())->countNumberOfGen();
    }

    function calculateNumOfMutation(){
        //me-return nilai mutationrate * jumlah individu untuk menghitung jumlah mutasi
        return round($this -> calculateMutationRate() * Parameters::POPULATION_SIZE);
    }

    function isMutation(){ 
        if($this->calculateNumOfMutation()>0){ //jika nilai return dari calculateNumOfMutation bernilai lebih dari 0 maka bernilai true
            return TRUE;
        }
    }

    function generateMutation($valueOfGen){
        if($valueOfGen===0){ //jika nilai value of gen bernilai 0 akan me-return 1 / mengubah nilai gen menjadi 1
            return 1;
        }
        else{//jika tidak maka return 0 / mengubah nilai gen menjadi 0
            return 0;
        }
    }

    function mutation(){//fungsi reprodusi ke dua proses mutasi individu 
        //mutasi adalah perubahan dalam suatu kromosom pada level gen atau perubahan pada level titik
        //perubahan ini berakibat pada terbentuknya variasi atau varian-varian individu / kromosom baru dan harapanya menjadi lebih baik

        if($this->isMutation()){ //memanggil fungsi isMutation jika bernilai true maka fungsi if akan dijalankan 

            for($i=0 ; $i <= $this->calculateNumOfMutation()-1;$i++){//melakukan perulangan sebanyak nilai return dari calculateNumOfMutation - 1
                
                $indexOfIndividu = Randomizer::getRandomIndexOfIndividu();//mengambil nilai dari fungsi getRandomIndexOfIndividu
                $indexofGen = Randomizer::getRandomIndexOfGen(); //mengambil nilai dari fungsi getRandomIndexOfGen
                $selectedindividu = $this->population[$indexOfIndividu]; //mengambil nilai array dari variabel population / mengambil data populasi


                echo"<br> Individu ke-";//urutan index dari individu yang terpilih untuk dimutasi
                //print_r($indexOfIndividu);
                echo"<br> Before Mutation: <br>";//output gen-gen dari individu yang terpilih untuk dimutasi
               // print_r($selectedindividu);

                echo"<br> Letak Gen yang dimutasi <br>";//letak gen yang akan dimutasi pada gen-gen individu yang terpilih
               // print_r($indexofGen);

                $valueOfGen = $selectedindividu[$indexofGen];//mengambil data invidu yang terpilih
                $mutatedGen = $this->generateMutation($valueOfGen);//melaukan mutasi dengan memanggil fungsi 
                $selectedindividu[$indexofGen] = $mutatedGen;//memasukan hasil mutasi ke gen yang akan diubah

                echo"<br> After Mutation: <br>";//hasil output individu yang telah dilakukan mutasi
                //print_r($selectedindividu);
                echo"<br>";

                $ret[] = $selectedindividu;
            }
            return $ret;
        }

    }

}

class Selection{
    //selection adalah menyeleksi individu individu terbaik yang merupakan hasil kombinasi dari populasi awal
    //dengan populasi offspring atau gabungan dari croossover dan mutasi 
    //proses seleksi digunakan teknik elitsm

    function __construct($population, $combinedOffsprings){
        //mengambail data construct pada pemanggilan fungsi
        $this -> population = $population;
        $this -> combinedOffsprings = $combinedOffsprings;
    }

    function createTemporaryPopulation(){
        //proses membuat populasi sementara atau temporary population
        foreach($this-> combinedOffsprings as $offspring){
            //mengambil data hasil offspring yang akan dimasukan pada variabel population  
            $this->population[] = $offspring;
        }

        //mereturn array populasi
        return $this->population;
    }

    function getVariabelValue($basePopulation, $fitTemporaryPopulation){
        foreach($fitTemporaryPopulation as $val){
            //proses menginputkan populasi sementara yang fit ke populasi bari hasil seleksi
            $ret[] = $basePopulation[$val[1]];
        }
        return $ret;
    }

    function sortFitTemporaryPopulation(){
        $tempPopulation = $this->createTemporaryPopulation();
       //Mndapatkan nilai return dari fungsi createTemporaryPopulation()

        $fitness = new Fitness;
        //setiap data array pada populasi sementara akan dihitung fitness valuenya
        foreach ($tempPopulation as $key => $individu){
            $fitnessValue = $fitness->calculateFitnessValue($individu);
            //jika populasi tersebut fit maka akan dimasukan ke array populasi sementara yang fit
            if($fitness->isFit($fitnessValue)){
                $fitTemporaryPopulation[] = [
                    $fitnessValue,
                    $key
                ];
            }
        }

        //proses dibawah ini mengurutkan array dari nilai fitness value dari yang terbesar
        rsort($fitTemporaryPopulation);

        //proses mengambil individu dari populasi sementara sebesar jumlah individu populasi awal
        $fitTemporaryPopulation = array_slice($fitTemporaryPopulation,0,Parameters::POPULATION_SIZE);
        foreach($fitTemporaryPopulation as $key => $val){
            echo"<br>";
            print_r($val);
        }

        //mereturn hasil fungsi getVariabelValue
        return $this->getVariabelValue($tempPopulation,$fitTemporaryPopulation);
    }

    function selectingIndividus(){
       $selected =  $this->sortFitTemporaryPopulation();
       //Mndapatkan nilai return dari fungsi sortFitTemporaryPopulation()
       //hasil populasi setelah diseleksi
       echo'<p></p>';
       $x=1;
       //proses output populasi yang telah dilakukan seleksi
       foreach ($selected as $key => $val){
           echo'<br>';
           echo $x.'. ';
           print_r($val);
           $x++;
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
$crossoverOffspring= $crossover->crossover();

//echo'Crossover Offsrping: <br>';
//print_r($crossoverOffspring);

echo"<p></p>";

//(new Mutation($population))->mutation();
$mutation = new Mutation($population);//membuat objek baru pada class mutation
if($mutation->mutation()){//jika fungsi mutation menghasilkan output maka fungsi dibawah dijalankan 

    $mutationOffSprings = $mutation->mutation();// mengambil salah satu individu yang telah dilakukan mutasi pada fungsi mutation
    echo '<br><br>Mutation offspring <br>';//output individu tersebut
    print_r($mutationOffSprings);
    echo"<p></p>";
    foreach($mutationOffSprings as $mutationOffSprings){
        $crossoverOffspring[] = $mutationOffSprings;//mengambil data individu-individu yang telah dimutasi
    }
}

//echo 'Mutation Offsprings <br>';//output seluruh individu ditambah individu yang telah dimutasi
//print_r($crossoverOffspring);

$fitness->fitnessEvaluation($crossoverOffspring);

$selection = new Selection($population,$crossoverOffspring); //membuat objek baru pada class Selection
$selection -> selectingIndividus(); //memanggil fungsi selectingIndividu() pada class Selection

// $individu = new individu;
// print_r($individu -> createRandomIndividu());