<?php

class Catalogue
{ 
    function createProductColumn($columns,$listOfRawProduct){ //pada proses ini digunakan untuk membuat kolom pada setiap data
        foreach(array_keys($listOfRawProduct) as $listOfRawProductKey){
            $listOfRawProduct[$columns[$listOfRawProductKey]] = $listOfRawProduct[$listOfRawProductKey];
            unset($listOfRawProduct[$listOfRawProductKey]);
        }
        return $listOfRawProduct;
    }

    function product($parameters){ 
        //pada proses ini dilakukan pengambilan data pada produk.txt yang nanti diinputkan ke $collectionOfListProduct
        $collectionOfListProduct = [];

        $raw_data = file($parameters['file_name']);
        foreach ($raw_data as $listOfRawProduct){
            $collectionOfListProduct[] = $this -> createProductColumn($parameters['columns'],explode(",",$listOfRawProduct));
            //proses memasukan data akan dipisah menjadi 2 kolom yang dibatasi dengan tanda koma pada poroduk.txt
        }

        // foreach($collectionOfListProduct as $listOfRawProduct){
        //     print_r($listOfRawProduct);
        //     echo '<br>';
        // }

        return [
            'product' => $collectionOfListProduct,
            'gen_length' => count($collectionOfListProduct)//jumlah gen / data nya pada produk
        ];

    }
}

class PopulationGenerator{
//pada proses ini dilakukan pembuatan populasi,dimana populasi itu adalah kumpulan individu, dan satu individu mewakili seluruh item produk / gen

    function createIndividu($parameters){
        $catalogue = new Catalogue;
        $lengthOfGen = $catalogue->product($parameters)['gen_length'];
        for ($i=0;$i<= $lengthOfGen-1;$i++){
            $ret[] = rand(0,1); 
            //dibuat bahwa setiap produk itu ambil atau tidak menggunakan opsi biner, 
            //jika diambil maka bernilai 1 dan jika tidak maka berniali 0
        }

        return $ret;
    }

    function createPopulation($parameters){
        for($i = 0;$i <= $parameters['population_size']; $i++){ //perulangan sebanyak population_size
           $ret[] =  $this -> createIndividu($parameters); // proses memanggil function createIndividu 
        }
        // print_r($ret);
        foreach($ret as $key => $val){ //proses output populasi
            print_r($val);
            echo '<br>'; 
        }
    }
}

$parameters = [ // digunakan untuk menyimpan variabel pada function yang nantinya dapat dipanggil
    'file_name' => 'produk.txt',
    'columns' => ['item', 'price'],
    'population_size' => 10
];

$katalog = new Catalogue; //membuat objek baru pada class Catalogue dengan nama $katalog
//print_r($katalog -> product($parameters));
$katalog -> product($parameters);
$initialPopulation = new PopulationGenerator;
$initialPopulation -> createPopulation($parameters);