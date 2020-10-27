<?php
//Принимает как аргумент три строки — фамилию, имя и отчество. Возвращает как результат их же, но склеенные через пробел
function getFullnameFromParts($surname, $name,  $middlename){
    return $surname."\x20".$name."\x20".$middlename;
}

//Принимает как аргумент одну строку — склеенное ФИО. Возвращает как результат массив из трёх элементов с ключами ‘name’, ‘surname’ и ‘patronomyc’
function getPartsFromFullname($fullName){
    $chunkName = []; 
    $startNum = 0;
    $strLen=mb_strlen($fullName);
    //Ищем вхождение пробела и добавляем в массив
    for($i=0; $i<$strLen; $i++)
    {
        if(mb_ord(mb_substr($fullName,$i,1))==32)
        {
            array_key_exists('surname',$chunkName) ? $chunkName['name'] = mb_substr($fullName, $startNum, $i-$startNum):$chunkName['surname'] = mb_substr($fullName, $startNum, $i-$startNum);
            $startNum = $i+1;
        }
        //После последнего пробела
        if($i==$strLen-1 && $startNum>0){
            array_key_exists('name',$chunkName) ? $chunkName['patronomyc'] = mb_substr($fullName, $startNum):$chunkName['Name'] = mb_substr($fullName, $startNum);
        }
    }
    return $chunkName;
}

//Принимает как аргумент строку, содержащую ФИО вида «Иванов Иван Иванович» и возвращающую строку вида «Иван И.», где сокращается фамилия и отбрасывается отчество
function getShortName($fullName){
    $nameParts = getPartsFromFullname($fullName);
    return $nameParts['surname']."\x20".mb_substr($nameParts['name'], 0, 1).".";
}

//Принимает как аргумент строку, содержащую ФИО (вида «Иванов Иван Иванович») и возвращает пол
function getGenderFromName($fullName){
    $nameParts = getPartsFromFullname($fullName);
    $genderMale=0;
    $genderFeMale=0;   
    //обработка фамилии
    if(mb_substr($nameParts['surname'], -1) =='в') $genderMale+=1;
    elseif(mb_substr($nameParts['surname'], -2)=='ва') $genderFeMale+=1;
    //обработка имени
    if(mb_substr($nameParts['name'], -1) =='й' || mb_substr($nameParts['name'], -1) =='н')  $genderMale+=1;
    elseif(mb_substr($nameParts['name'], -1) =='а') $genderFeMale+=1;
    //обработка отчества
    if(mb_substr($nameParts['patronomyc'], -2) =='ич')  $genderMale+=1;
    elseif(mb_substr($nameParts['patronomyc'], -3) =='вна') $genderFeMale+=1;    
    //возврат результата
    return $genderMale<=>$genderFeMale;
}

/*Определения полового состава аудитории. Как аргумент в функцию передается массив.Как результат функции возвращается информация в следующем виде
Гендерный состав аудитории:
---------------------------
Мужчины - 55.5%
Женщины - 35.5%
Не удалось определить - 10.0% */
function getGenderDescription($auditory){
    $results;
    $maleResult=0;
    $femaleResult=0;   
    $undefinedResult=0;      
    //обработка всего массива
    foreach($auditory as $person)
    {
        $results[]=getGenderFromName($person['fullname']);
    }
    echo "Мужчины - ".round(count(array_filter($results, function($num)
    {
        if ($num == 1) return true;
        else return false;
    }))/count($results),2).'%'.'<br>';;
    echo "Женщины - ".round(count(array_filter($results, function($num)
    {
        if ($num == -1) return true;
        else return false;
    }))/count($results),2).'%'.'<br>';;
    echo "Не удалось определить - ".round(count(array_filter($results, function($num)
    {
        if ($num == 0) return true;
        else return false;
    }))/count($results),2).'%'.'<br>';;   
}

//Определения «идеальной» пары. Как первые три аргумента в функцию передаются строки с фамилией, именем и отчеством (именно в этом порядке). При этом регистр может быть любым: ИВАНОВ ИВАН ИВАНОВИЧ, ИваНов Иван иванович. Как четвертый аргумент в функцию передается массив
function  getPerfectPartner($surname, $name, $middlename, $auditory){
    $normalisedName =  getFullnameFromParts(mb_convert_case($surname, MB_CASE_TITLE),mb_convert_case($name, MB_CASE_TITLE),mb_convert_case($middlename, MB_CASE_TITLE)); 
    $curGender=getGenderFromName($normalisedName);
    $pairPerson=null;
    do{
        $pairPerson = $auditory[rand(0,count($auditory)-1)];
        if(getGenderFromName($pairPerson['fullname'])==$curGender) $pairPerson=null;
    } while($pairPerson==null);
    echo getShortName($normalisedName).'+'.getShortName($pairPerson['fullname']).'='.'<br>';
    echo "♡ Идеально на ".rand(50,100)."% ♡".'<br>';;
}