<?php

Class Parameters{ // membuat class untuk menyimpan atribut-atribut
    const FILE_NAME = 'produk.txt';
    const COLUMNS = ['item', 'price'];
    const POPULATION_SIZE = 10;
    const BUDGET =  280000;
    const STOPING_VALUE = 30000;
}

class Catalogue
{
    
    function createProductColumn($listOfRawProduct){ //pada proses ini digunakan untuk membuat kolom pada setiap data
        foreach(array_keys($listOfRawProduct) as $listOfRawProductKey){
            $listOfRawProduct[Parameters::COLUMNS[$listOfRawProductKey]] = $listOfRawProduct[$listOfRawProductKey];
            unset($listOfRawProduct[$listOfRawProductKey]);
        }
        return $listOfRawProduct;
    }

    function product(){
        //pada proses ini dilakukan pengambilan data pada produk.txt yang nanti diinputkan ke $collectionOfListProduct
        $collectionOfListProduct = [];

        $raw_data = file(Parameters::FILE_NAME);
        foreach ($raw_data as $listOfRawProduct){
            $collectionOfListProduct[] = $this -> createProductColumn(explode(",",$listOfRawProduct));
             //proses memasukan data akan dipisah menjadi 2 kolom yang dibatasi dengan tanda koma pada poroduk.txt
        }

        // foreach($collectionOfListProduct as $listOfRawProduct){
        //     print_r($listOfRawProduct);
        //     echo '<br>';
        // }

        return $collectionOfListProduct; //mengeembalikan nilai / return variabel collectionoflistproduct

    }
}

Class individu{
    //pada class ini digunakna untuk membuat individu dan satu individu mewakili seluruh item produk / gen
    function countNumberOfGen(){
        $catalogue = new Catalogue;
        return count($catalogue -> product()); //mengeembalikan nilai / return jumlah function product() dihutung jumlah product ada berapa
    }

    function createRandomIndividu(){
        for ($i=0;$i<= $this->countNumberOfGen() - 1;$i++){
            $ret[] = rand(0,1);
            //dibuat bahwa setiap produk itu ambil atau tidak menggunakan opsi biner, 
            //jika diambil maka bernilai 1 dan jika tidak maka berniali 0
        }
        return $ret; //mengeembalikan nilai / return
        
    }
}

class Population{
//pada proses ini dilakukan pembuatan populasi,dimana populasi itu adalah kumpulan individu, dan satu individu mewakili seluruh item produk / gen
    function createRandomPopulation(){
        $individu = new individu;
        for($i = 0;$i <= Parameters::POPULATION_SIZE - 1 ; $i++){ //perulangan sebanyak population_size
           $ret[] =  $individu -> createRandomIndividu(); // proses memanggil function createrandomIndividu
        }
        return $ret; //mengeembalikan nilai / return
        
    }
}

class Fitness{
    //pada studi kasus ini menentukan parcel / individu yang berkualitas
    //pada proses ini dilakukan seleksi produk yang akan dimasukan pada fungsi fitness
    //fungsi fitness itu sendiri adalah mennetukan item-item produk paling banyak dalam suatu individu 
    //juga memiliki biaya dengan jumlah paling mendekati dengan budget dan tidak boleh lebih

    function selectingItem($individu){
        $catalogue = new Catalogue;
        foreach($individu as $individuKey => $binaryGen){//perulangan sebanyak jumlah individu
            if($binaryGen === 1){//jika ilai retrun dari individu bernilai 1 maka produk adakn dimasukan ke dalam variabel ret
                $ret[] = [
                    'selectedKey' => $individuKey,
                    'selectedPrice' => $catalogue -> product()[$individuKey]['price']
                ]; 
            }
            
        }
        return $ret;
    }

    function calculateFitnessValue($individu){
        //proses ini digunakan untuk menghitung harga dari produk-produk yang masuk pada parcel atau yang memiliki nilai 1
        return array_sum(array_column($this -> selectingItem($individu),'selectedPrice'));
       
    }

    function countSelectedItem($individu){
        //proses ini digunakan untuk menghitung jumlah produk-produk yang masuk pada parcel atau yang memiliki nilai 1
        return count($this->selectingItem($individu));
    }

    function searchBestIndividu($fits,$maxItem,$numberOfIndividuMaxItem){
        //pada proses ini mennetukan suatu individu yang paling berkualitas dari produk-produk yang lolos penyeleksian pada fungsi fitness
        if($numberOfIndividuMaxItem === 1){
            $index = array_search($maxItem, array_column($fits,'numberOfSelectedItem'));
            return $fits[$index];
            echo'<br>';
            //jika individu hasil seleksi yang memiliki produk paling banyak hanya terdapat satu maka akan langsung dioutputkan
        }
        else{ 
            // jika individu hasil seleksi yang memiliki produk paling banyak terdapat lebih dari satu
            // maka individu tersebut harus diseleksi lagi
            foreach($fits as $key => $val){
                if($val['numberOfSelectedItem'] === $maxItem){
                    echo $key.' '.$val['fitnessValue'].'<br>';
                    $ret[] =[
                        'individuKey' => $key,
                        'fitnessValue' => $val['fitnessValue']

                    ];
                }
            }
            //jika nilai fitness value dari individu-individu sama / unique maka akan dipilih secara acak 
            if(count(array_unique(array_column($ret, 'fitnessValue'))) === 1){
                $index = rand(0, count($ret) - 1);
            }
            else{
                $max = max(array_column($ret,'fitnessValue'));
                $index = array_search($max,array_column($ret,'fitnessValue'));
            }
            echo '<br>Hasil: ';
            //print_r($ret[$index]);
            return $ret[$index]; 
        }
    }

    function isFound($fits){

        $countedMaxItem = array_count_values(array_column($fits,'numberOfSelectedItem'));
        print_r($countedMaxItem); //jumlah item produk dari semua dindividu yang akan dikelompokan 
        echo '<br>';
        $maxItem = max(array_keys($countedMaxItem));
        echo $maxItem;//jumlah item-item produk paling banyak pada individu
        echo '<br>';
        echo $countedMaxItem[$maxItem];//jumlah individu yang memiliki item-item produk paling banyak
        echo '<br>';
        $numberOfIndividuMaxItem = $countedMaxItem[$maxItem];

        $bestFitnessValue = $this -> searchBestIndividu($fits,$maxItem,$numberOfIndividuMaxItem)['fitnessValue'];
        //proses menghitung fitness value dari individu-individu yang telah diseleksi

        //print_r($bestFitnessValue)['fitnessValue'];
        echo '<br>Best fitness value: '.$bestFitnessValue;//output fitness value individu / parcel paling berkualitas
 
 
        $residual = Parameters::BUDGET - $bestFitnessValue;
        //menghitung nilai residual dengan mengurangi nilai budget yang dimiliki dengan harga fitness atau harga parcel
        echo 'Residual: '. $residual;

        if($residual <= Parameters::STOPING_VALUE && $residual > 0){
            //jika nilai residual lebihdari 0 dan kurang dari sama dengan stoping value maka return true
            //yang nantinya digunakan untuk mengecek individu/parcel tersebut paling berkualitas denngan harga yang paling mendekati budget
            return True;
        }
    }

    function isFit($fitnessValue){
        //jika nilai residual kurang dari sama dengan stoping value maka return true
        if($fitnessValue <= Parameters::BUDGET){
            return True;
        }
    }

    function fitnessEvaluation($population){
        $catalogue = new Catalogue;
        foreach($population as $listOfindividuKey => $listOfIndividu){
            //proses mengoutputkan individu-individu pada populasi
            echo 'Individu-'. $listOfindividuKey. '<br>';
            foreach ($listOfIndividu as $individuKey => $binaryGen){
                echo $binaryGen.'&nbsp;&nbsp';
                print_r($catalogue -> product()[$individuKey]);
                echo '<br>';
            }
            //memanggil fungsi untuk menghitung fitness value
            $fitnessValue = $this->calculateFitnessValue($listOfIndividu); 
            //memanggil fungsi untuk menentukan produk-produk yang terpilih / bernilai 1
            $numberOfSelectingItem = $this -> countSelectedItem($listOfIndividu);

            echo 'Max Item: '.$numberOfSelectingItem;
            echo  ' Fitness Value :'. $fitnessValue;

            if($this -> isFit($fitnessValue)){
                //jika nilai value kurang dari budget maka fit
                echo '(Fit)';
                $fits[] = [
                    'selectedIndividuKey' => $listOfindividuKey,
                    'numberOfSelectedItem' => $numberOfSelectingItem,
                    'fitnessValue' => $fitnessValue
                ];
                print_r($fits); 
            }
            else{
                //jika nilai value lebih dari budget maka no fit
                echo '(Not Fit)';
            }
           
            echo '<p>';
        }

        if($this -> isFound($fits)){
            //jika individu / parcel paling berkualitas denngan harga yang paling mendekati budget
            //dan sisa dari budget tidak melebihi stoping value maka parcel ketemu
            echo ' Found';
        }
        else{
            //jika tidak maka dilakukan pencarian ulang sampai ketemu
            echo'>> next generation';
        }
       
    }
}

// $parameters = [
//     'file_name' => 'produk.txt',
//     'columns' => ['item', 'price'],
//     'population_size' => 10
// ];

// $katalog = new Catalogue;
// // print_r($katalog -> product($parameters));
// $katalog -> product($parameters);

$initialPopulation = new Population; //membuat objek baru pada class Population dengan nama $initialpopulation
$population = $initialPopulation -> createRandomPopulation();

    $fitness = new Fitness;
    $fitness -> fitnessEvaluation($population);

// $individu = new individu;
// print_r($individu -> createRandomIndividu());