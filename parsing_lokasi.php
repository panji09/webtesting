<?php
  /*
  author	: panji adhie musthofa
  version	: 0.2
  description	: script ini mengconvert data provinsi, kabkota, kecamatan, desa di dalam web http://mfdonline.bps.go.id menjadi csv.
  
  change log
  v 0.2 : konversi provinsi, kabkota, kecamatan, kelurahan/desa
  v 0.1 : konversi provinsi, kabkota, kecamatan
  */
  //delete file
  unlink('output/provinsi.csv');
  unlink('output/kabkota.csv');
  unlink('output/kecamatan.csv');
  unlink('output/desa.csv');
  
  include('lib/ganon.php');
  include('lib/simple_html_dom.php');
  
  $url="http://mfdonline.bps.go.id/index.php?link=rekap_wil_desa";
  $html = file_get_dom($url);
  $entry=$html('div.entry', 0);
  $form=$entry('form',0);
  $table=$form('table',0);
  $tr=$table('tr',0);
  $div=$tr('div',2);
  $select=$div('select',0);

  $html2=str_get_html($select->getInnerText());
  $provinsi = fopen("output/provinsi.csv", "w");
  fwrite($provinsi, '"id","nama"'."\n");
  
  $kabkota = fopen("output/kabkota.csv", "a");
  fwrite($kabkota, '"id","id_prov","nama"'."\n");
  
  $kecamatan = fopen("output/kecamatan.csv", "a");
  fwrite($kecamatan, '"id","id_kabkota","nama"'."\n");
  
  $desa = fopen("output/desa.csv", "a");
  fwrite($desa, '"id","id_kecamatan","nama"'."\n");
  
  foreach($html2->find('option') as $element){
    parse_str($element->value);
    if($element->innertext != '----------'){
      echo "-------".ucwords(strtolower(substr($element->innertext,3)))."-------\n";
      fwrite($provinsi,'"'.$pr.'","'.ucwords(strtolower(substr($element->innertext,3)))."\"\n");
      
      /*
      save kabkota
      */
      
      kabkota($pr);
      
      echo "-------end ".ucwords(strtolower(substr($element->innertext,3)))."-------\n";
    }
  }
  
  fclose($desa);
  fclose($kecamatan);
  fclose($kabkota);
  fclose($provinsi);
  
  function kabkota($id_prov){
    $url="http://mfdonline.bps.go.id/index.php?link=rekap_wil_desa&pr=".$id_prov;
    $html = file_get_dom($url);
    $entry=$html('div.entry', 0);
    $form=$entry('form',0);
    $table=$form('table',0);
    $tr=$table('tr',1);
    $div=$tr('div',2);
    $select=$div('select',0);
    
    $html2=str_get_html($select->getInnerText());
    $kabkota = fopen("output/kabkota.csv", "a");
    foreach($html2->find('option') as $element){
      parse_str($element->value);
      if($element->innertext != '----------'){
	$pre=(substr($kb,0,1)==7 ? 'Kota' : 'Kab.');
	fwrite($kabkota,'"'.$pr.$kb.'",'.'"'.$pr.'","'.$pre.' '.ucwords(strtolower(substr($element->innertext,3)))."\"\n");
	
	/*
	save kecamatan
	*/
	kecamatan($pr,$kb);
      }
      
    }
    fclose($kabkota);
  }
  
  function kecamatan($id_prov, $id_kabkota){
    $url="http://mfdonline.bps.go.id/index.php?link=rekap_wil_desa&pr=".$id_prov."&kb=".$id_kabkota;
    $html = file_get_dom($url);
    $entry=$html('div.entry', 0);
    $form=$entry('form',0);
    $table=$form('table',0);
    $tr=$table('tr',2);
    $div=$tr('div',2);
    $select=$div('select',0);
    
    $html2=str_get_html($select->getInnerText());
    $kecamatan = fopen("output/kecamatan.csv", "a");
    foreach($html2->find('option') as $element){
      parse_str($element->value);
      if($element->innertext != '----------'){
	fwrite($kecamatan,'"'.$pr.$kb.$kc.'",'.'"'.$pr.$kb.'","'.ucwords(strtolower(substr($element->innertext,3)))."\"\n");
	
	/*
	save desa
	*/
	desa($pr,$kb,$kc);
      }
      
    }
    fclose($kecamatan);
  }
  function desa($id_prov, $id_kabkota, $id_kecamatan){
    $url="http://mfdonline.bps.go.id/index.php?link=rekap_wil_desa&pr=".$id_prov."&kb=".$id_kabkota."&kc=".$id_kecamatan;
    $html = file_get_dom($url);
    $entry=$html('tr.table_content td:first-child');
    $desa = fopen("output/desa.csv", "a");
    foreach($entry as $element) {
      if($element->getInnerText()!='<strong>TOTAL</strong>'){
	$part = explode(" \xA0 ", $element->getInnerText());
	
	/*
	part[0] -> kode desa
	part[1] -> nama desa
	*/
	fwrite($desa,'"'.$id_prov.$id_kabkota.$id_kecamatan.$part[0].'",'.'"'.$id_prov.$id_kabkota.$id_kecamatan.'","'.ucwords(strtolower($part[1]))."\"\n");
      }
    }
    fclose($desa);
  }
?>