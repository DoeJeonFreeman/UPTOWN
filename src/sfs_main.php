<?php

// read date time from 'dateTime.dat'
$dateTimeFromFile = trim(fgets(fopen("./dateTime.dat", "r"), 1000));

// doejeonFreeman June 2016
	$meText = file_get_contents('./timeseriesChartData.properties', true);
	require_once('properties.php');
	$property = parse_properties($meText);
// doejeonFreeman June 2016


///////////////////////////////////////////////////////////////////////////////////////////////////////////////
// 지점가이던스용 Floating Menu
$tempDate = substr($dateTimeFromFile, 0, 4)."-".substr($dateTimeFromFile, 4, 2)."-".substr($dateTimeFromFile, 6, 2);
$tempDate .= " ".substr($dateTimeFromFile, 8, 2).":".substr($dateTimeFromFile, 10, 2);

$quick_menu_div_point = "
		<select id='floatingMenuPoint' onChange='changeSelectPoint();' style='margin-left: -1.5px; width: 190px;'>";

for ($i=14;$i>=0;$i--) {
	$expDateTime = explode(" ", $tempDate);
	$expDate = explode("-", $expDateTime[0]);
	$expTime = explode(":", $expDateTime[1]);
	
	$quick_menu_div_point .= "<option value='" . $i . "' ref='".$expDate[0].sprintf('%02d', $expDate[1]).sprintf('%02d', $expDate[2]).sprintf('%02d', $expTime[0])."00'>". $expDate[0] . "년 " . sprintf('%02d', $expDate[1]) . "월 " . sprintf('%02d', $expDate[2]) . "일 " . $expTime[0] . "시(UTC)</option>";
	date_default_timezone_set('Asia/Seoul');
	$tempDate = date("Y-n-d H:i", strtotime($tempDate . " -12Hours") );
}
$quick_menu_div_point .= "</select>";

///////////////////////////////////////////////////////////////////////////////////////////////////////////////
// 격자슬라이드용 Floating Menu
// 격자가이던스에 나오는 콤보박스를 생성 (12시간 간격)
$tempDate = substr($dateTimeFromFile, 0, 4)."-".substr($dateTimeFromFile, 4, 2)."-".substr($dateTimeFromFile, 6, 2);
$tempDate .= " ".substr($dateTimeFromFile, 8, 2).":".substr($dateTimeFromFile, 10, 2);

$quick_menu_div_lattice = "<select id='floatingMenuLattice' onChange='changeSelectLattice();' style='margin-left:-1.5px;width:190px;'>";
for ($j=14,$k=0;$j>=0;$j--,$k++) {

	$expDateTime = explode(" ", $tempDate);
	$expDate = explode("-", $expDateTime[0]);
	$expTime = explode(":", $expDateTime[1]);
	
	// 격자가이던스에 나오는 그림들을 위해 변수에 화일이름 저장
	$fileNameForLatticeImages[$k] = $expDate[0] . sprintf('%02d', $expDate[1]) . sprintf('%02d', $expDate[2]) . sprintf('%02d', $expTime[0]);
	
	$quick_menu_div_lattice .= "<option value='" . $j . "' ref='".$expDate[0].sprintf('%02d', $expDate[1]).sprintf('%02d', $expDate[2]).sprintf('%02d', $expTime[0])."00'>". $expDate[0] . "년 " . sprintf('%02d', $expDate[1]) . "월 " . sprintf('%02d', $expDate[2]) . "일 " . $expTime[0] . "시(UTC)</option>";

	$tempDate = date("Y-n-d H:i", strtotime($tempDate . " -12Hours") );
}
$quick_menu_div_lattice .= "</select>";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=euc-kr" />
<title>MOS 모델 예보가이던스</title>

<!-- JQuery Essential -->
<link rel='stylesheet' href='include/mainStyle.css' type='text/css' media='' />
<script type="text/javascript" src="include/jquery-1.6.1.min.js"></script>

<!-- JQuery UI -->
<link rel='stylesheet' href='css/cupertino/jquery-ui-1.8.16.custom.css' type='text/css' media='' />
<script type="text/javascript" src="include/jquery-ui-1.8.16.custom.min.js"></script>


<!-- JS file for Flex Loader -->
<script language="javascript" src="include/AC_OETags.js"></script>

<script type="text/javascript">

//June 2016///////////////////doejeonFreeman/////////////////////////////////////////////
	//var mePropertyArray = JSON.parse('php echo json_encode($property); ?>');
	
	var isVERBOSE = true; 
	
	//convert php associative array to js array haha
	var mePropertyArray = <?php echo json_encode($property); ?>; // don't use quotes
	/* $.each(mePropertyArray, function(key, value) {
	    sysout('stuff : ' + key + " == " + value);
	}); */

	function getTimeseriesXMLDataPropertyValue(key) {
		return $.trim(mePropertyArray[key]);
	}

	function getPropertyKeyStr(modelStr){
		var key;
		if(modelStr=='PMOS'){
			key = 'SHRT.RDPS_MOS';
		}else if(modelStr=='RDPS'){
			key = 'SHRT.RDPS';
		}else if(modelStr=='ECMW'){
			key = 'SHRT.ECMWF_MOS';
		}else if(modelStr=='SBEST'){ 
			key = 'SHRT.BEST';
		}else if(modelStr=='GDPS'){ 
			key = 'MEDM.GDPS_MOS';
		}else if(modelStr=='w_ECMW'){ 
			key = 'MEDM.ECMWF_MOS';
		}else if(modelStr=='EPSG'){ 
			key = 'MEDM.EPSG_MOS';
		}else if(modelStr=='etc' || modelStr=='PYEONGCHANG'){ 
// 		}else if(modelStr=='SSPS'){ 
			key = 'SHRT.SSPS';
		}
		return key
	}

	function sysout(str) {
		if(!isVERBOSE) return;
		if(window['console']!='undefined') console.log(str);
	}
	
//June 2016///////////////////doejeonFreeman/////////////////////////////////////////////
	

	//////////////////////////////////////////////////////////////////////////////////////
	// SWF Loading part Starts
	//////////////////////////////////////////////////////////////////////////////////////	// Globals
	// Major version of Flash required
	var requiredMajorVersion = 9;
	// Minor version of Flash required
	var requiredMinorVersion = 0;
	// Minor version of Flash required
	var requiredRevision = 124;

	var flashvars = { useExternalInterface: "true" }
	var jsReady = false;

        // john added : check for file exist : if temp file is not exist then stop avoid unlimited loop
        var isFirst = true;
	// for the slideShow interval
	var tempSlideIndex;
	var tid;

	// 슬라이드용 이미지 인덱스/슬라이드용 Label용 배열
	var latticeImgIndex = 0;
	var sliderSteps = new Array();
	sliderSteps.length = 21;

	function isReady() {
	  return jsReady;
	}

	function pageInit() {
	  jsReady = true;
	}


	//var added. Doe
	var movie=false;
	function thisMovie(movieName) {
		if (navigator.appName.indexOf("Microsoft") != -1) {
			if (typeof (window[movieName].sendMsgToFlexApp) == 'function') {
//				alert("< IE9");
				movie = window[movieName];
			}
			else if (typeof (document[movieName].sendMsgToFlexApp) == 'function') {
//				alert(">= IE9");
				movie = document[movieName];
			}
		}
		else {
//			alert("NON IE");
			movie = document[movieName];
		}
		return ((movie) ? true : false);
	}

	function sendMsgToActionScript(msg) {
//		thisMovie('SFS_meteogram.swf').sendMsgToFlexApp(msg); //do not use this stuff
		if(thisMovie("SFS_meteogram.swf")){
			movie.sendMsgToFlexApp(msg);
		}else{
			alert('Failed to initialize');
		}
	}

	function cleanupSWFs() {
		var objects = document.getElementsByTagName("OBJECT");
		for (var i=0; i < objects.length; i++) {
			objects[i].style.display = 'none';
			for (var x in objects[i]) {

				if (typeof objects[i][x] == 'function') {
					objects[i][x] = function() {};
				}
			}
		}	
	}

	function prepUnload(){
		__flash_unloadHandler = function(){};
		__flash_savedUnloadHandler = function(){};
		if (typeof window.onunload == 'function') {
			var oldUnload = window.onunload;
			window.onunload = function() {
				cleanupSWFs();
				oldUnload();
			}
		} else {
		//		window.onunload = deconcept.SWFObjectUtil.cleanupSWFs;
		}
	}



	/**
	* alt by djfreeman (2016.06.07.)
	*  --> getPropertyKeyStr(swfXmlType1)
	*/
	function getCurrentStatusForSwf() {
		
		var swfDataType = $("input:radio[name=rdo_subTabPoint]:checked").attr("ref");      // variable abbreviation e.g. POP, S12
		var swfXmlType = $("input:radio[name=rdo_subTabModePoint]:checked").attr("ref");   // filename prefix e.g. DFS_MEDM_STN_GDPS_PMOS	
		var swfXmlType1 = $("input:radio[name=rdo_subTabModePoint]:checked").val();        // base model name abbreviation e.g. EPSG
		var swfDateTime = $("#floatingMenuPoint option:selected").attr("ref");             // selected yyyymmddhh 
		var swfStationNo = $("#stndate option:selected").val();                            // selected administrative division code

// sysout('[ori] '+swfDataType + ' / ' + swfXmlType1);

		swfXmlType = getPropertyKeyStr(swfXmlType1);
		if ((swfXmlType1=="PYEONGCHANG" || swfXmlType1=="etc")&&(swfDataType=="POP")) {
// 			   swfXmlType1 = "SSPS";
               swfXmlType = getPropertyKeyStr(swfXmlType1);
			   swfDataType = "VIS";
		}else if (swfXmlType1=="GDPS" || swfXmlType1=="EPSG" || swfXmlType1=="w_ECMW") {
            swfXmlType = getPropertyKeyStr(swfXmlType1);
 	 		if(swfDataType=="RN3" || swfDataType=="SN3"){
			    swfDataType = swfDataType.replace("N3","12"); 
 	 		}else if(swfDataType=="T3H"){
				swfDataType = "MMX";
 	 		}	
		} else if(swfXmlType1=="PMOS" && swfDataType=="SN3"){
			swfDataType = 'SN6';
		}
		/* else if(swfXmlType.indexOf("BEST") != -1 && swfXmlType.indexOf("MEDM") != -1){
			if(swfDataType =="T3H") swfDataType = "MMX"; 
			else if(swfDataType =="SN3") swfDataType = "S12";
			else if(swfDataType =="RN3") swfDataType = "R12";
		} */
		
		var swfParam = swfXmlType + " " + swfDataType + " " + swfDateTime + " " + swfStationNo;

		if ((swfDataType=="MUL")) {
	    	swfParam = swfXmlType + " " + (swfXmlType.indexOf("MEDM")!=-1? "MMX" : "T3H") + " " + swfDateTime + " " + swfStationNo + " isMulti";
			if(swfXmlType1=="PYEONGCHANG") swfParam+=" isPYEONGCHANG2018";
		}

// sysout('[alt] '+swfDataType + ' / ' + swfXmlType1);
sysout('getCurrentStatusForSwf [passing params to FlexApp] '+ swfParam);

		return swfParam;
	}
	

	var mergedModel;
	//flex에서 얘 호출할꺼니까 얘 구현하세요 
	function checkWhetherMergedModelCheckboxIsCheckedOrNot(isChecked){
		////sysout('checkWhetherMergedModelCheckboxIsCheckedOrNot\(isChecked\)\nisChecked == ' + isChecked);
		$('#etc').attr('disabled', isChecked);
		
		var beforeModelAttr = $("input:radio[name=rdo_subTabModePoint]:checked").attr("ref");
		var beforeModelVal = $("input:radio[name=rdo_subTabModePoint]:checked").val();
		var shrtChecked = $("#shrt").attr("checked");
		var model_seq;
			if(beforeModelVal=='PMOS'){
				model_seq = 0;
			}else if(beforeModelVal=='RDPS'){
				model_seq = 1;
			}else if(beforeModelVal=='KWRF'){
				model_seq = 2;
			}else if(beforeModelVal=='ECMW'){
				model_seq = 3;
			}else if(beforeModelVal=='GDPS'){ // GDPS	PMOS2->GDPS changed sj 20140103
				model_seq = 5;
			}else if(beforeModelVal=='w_ECMW'){ // 중기ECMWF
				model_seq = 6;
			}else if(beforeModelVal=='EPSG'){ // 중기ECMWF
				model_seq = 7;
			}else if(beforeModelVal=='etc'){ // 중기ECMWF
				model_seq = 8;
			//=======================================doeJeon	
			}else if(beforeModelVal=='SBEST'){ 
				model_seq = 9;
			}else if(beforeModelVal=='MBEST'){ 
				model_seq = 10;
			}
			//=======================================doeJeon	
//			////sysout(model_seq);
		if(isChecked == true){
			$("a[id^='a_subTabPointMode_']").removeClass("highlight");
			$("a[id^='a_subTabPoint_']").removeClass("dummy");	// 전체를 다 Enable
			if(shrtChecked != 'checked'){						// 중기는 2/4/6 비활성
				$("#a_subTabPoint_2").addClass("dummy");
				$("#a_subTabPoint_4").addClass("dummy");
				$("#a_subTabPoint_6").addClass("dummy");
			}
			//$("#a_subTabPoint_8").addClass("dummy");
			mergedModel = true;
			//document.getElementById("rd_bt0").disabled = true;
			//$("#rd_bt0").attr("disabled",true);
			//$("#rd_bt0").attr("disabled","disabled");
			$("a[id^='rd_bt']").prop("disabled",true);
			$("a[id^='a_subTabPointMode_']").prop("disabled",true);
			//$("#td_radioTabModePoint_"+model_seq).find(":radio").attr("checked","false");
		} else {
			$("a[id^='a_subTabPointMode_"+model_seq+"']").addClass("highlight");
			var currentSubTab = parseInt($("input:radio[name=rdo_subTabPoint]:checked").val());
			checkDisabledSubTabs(currentSubTab);
			mergedModel = false;
			$("a[id^='rd_bt']").removeAttr("disabled");
			$("a[id^='a_subTabPointMode_']").removeAttr("disabled");
		}
	}
	
	
	//doe
	var InitInfoUserStartPage;
	//meBest
	//meBest
	//meBest
	function getInitInfo(userConfiguredGroupCode, baseModel, isMEDM, isPyeongchang) {
		// //sysout('getInitInfo\(params..\)\n'+userConfiguredGroupCode+' / '+baseModel+' /isMEDM '+isMEDM + ' /isPyeongchang== ' + isPyeongchang);
		if(userConfiguredGroupCode == "8318" && baseModel == "PMOS" && isMEDM == "false"){
		// if(userConfiguredGroupCode == "8318" && baseModel == "BEST" && isMEDM == "false"){ //meBest
			InitInfoUserStartPage = false;
		} else {
			InitInfoUserStartPage = true;
		}
		
		if(baseModel!='null'){
		
			//whether shortTerm or not
			(isMEDM=='true')? $("#medm").attr("checked","checked"):$("#shrt").attr("checked","checked");
			
			//if (isMEDM == 'true') {
		//		showModel('Weekly');
		//	} else {
		//		showModel('ShortTime');
		//	}
			
			//set userConfiguredGroupCode
			if(isMEDM == 'true') {
				InitstndateSelectOption("Weekly",$("input:radio[name=chk_info]:checked").val());
			} else {
				if(baseModel == 'SSPS') {
					if(isPyeongchang=='true'){
						InitstndateSelectOption('QueenYuna','secondParamIsNotUsedLocally');
					}else{
						InitstndateSelectOption("etc",$("input:radio[name=chk_info]:checked").val()); //doejeon 평창은 시작페이지 설정 사용못하게
					}
				} else {
					InitstndateSelectOption("ShortTime",$("input:radio[name=chk_info]:checked").val()); 
				}
			}
	     	$("#stndate option[value="+userConfiguredGroupCode+"]").attr("selected","selected");
		

			//set baseModel
			$("a[id^='a_subTabPointMode_']").removeClass("highlight");
			$("input[name=rdo_subTabModePoint][value="+baseModel+"]").attr("checked","checked");
			var model_seq;
			if(baseModel=='PMOS'){
				model_seq = 0;
			}else if(baseModel=='RDPS'){
				model_seq = 1;
			}else if(baseModel=='KWRF'){
				model_seq = 2;
			}else if(baseModel=='ECMWF'){
				model_seq = (isMEDM!='true')? 3:6;
			}else if(baseModel=='PMOS2'){ // GDPS
				model_seq = 5;
			}else if(baseModel=='EPSG'){
				model_seq = 7;
			}else if(baseModel=='SSPS'){
				model_seq = (isPyeongchang=='true')?11 : 8; //meFine
				$("#etc").attr("checked","checked"); 
			//=======================================doeJeon	
			} else if(baseModel == 'BEST'){
				model_seq = (isMEDM!='true')? 9:10;
			}	
			//=======================================	
			//meBest
			if ((model_seq > 3 && model_seq < 8) || model_seq == 10) { //doeJeon add condition || model_seq == 10 
				document.getElementById('tbl_weekly').style.display="";
 				document.getElementById('tbl_modePoint').style.display="none";
 				document.getElementById('tbl_etc').style.display="none";
				//InitstndateSelectOption("Weekly",$("input:radio[name=chk_info]:checked").val());
			} else if (model_seq == 8 || model_seq == 11) { //meFine
				document.getElementById('tbl_weekly').style.display="none";
				document.getElementById('tbl_etc').style.display="";
 				document.getElementById('tbl_modePoint').style.display="none";
				//InitstndateSelectOption("etc",$("input:radio[name=chk_info]:checked").val());
			
			} else {
				document.getElementById('tbl_weekly').style.display="none";
				document.getElementById('tbl_etc').style.display="none";
 				document.getElementById('tbl_modePoint').style.display="";
				//InitstndateSelectOption("ShortTime",$("input:radio[name=chk_info]:checked").val());
			}
			
			$("#td_radioTabModePoint_"+model_seq).find(":radio").attr("checked","checked");
			$("a[id^='a_subTabPointMode_"+model_seq+"']").addClass("highlight");
			//alert('messageFromMe: '+baseModel+' ['+model_seq+']')
			var currentSubTab = parseInt($("input:radio[name=rdo_subTabPoint]:checked").val());//add sj 20131213
			// //sysout('checkDisabledSubTabs(currentSubTab)::  ' + currentSubTab)
			// 서브메뉴를 선별하여 보여준다
			checkDisabledSubTabs(currentSubTab);
		}
		
		var swfParam = getCurrentStatusForSwf();
		if (isFileThere(swfParam)==true) {//데이터 있는지 검사
			return swfParam;
		} else {							//없을경우 디폴트로
			$("#shrt").attr("checked","checked");
			$("a[id^='a_subTabPointMode_']").removeClass("highlight");
			$("input[name=rdo_subTabModePoint][value=PMOS]").attr("checked","checked");
			document.getElementById('tbl_weekly').style.display="none";
			document.getElementById('tbl_etc').style.display="none";
 			document.getElementById('tbl_modePoint').style.display="";
 			
 			//meBest  9는 베스트고 0은 단기 pmos여 
			// $("#td_radioTabModePoint_9").find(":radio").attr("checked","checked");
			// $("a[id^='a_subTabPointMode_9']").addClass("highlight");
			
			$("#td_radioTabModePoint_0").find(":radio").attr("checked","checked");
			$("a[id^='a_subTabPointMode_0']").addClass("highlight");
			return getCurrentStatusForSwf();
		}
		
	}
	
	//doe
	function getPersonalizationSettingDataFromFlash(value){
		//alert(value);
	}


	function addPersonalizationSettingPopup(url){
		//alert(url);
		var width = "550";
		var height = "454";
		dialog = window.open(url, "", "resizable=no, toolbar=no, titlebar=no, toolbar=no, width=" + width + ",height=" + height + ",menubar=no,status=no,scrollbars=auto,left="+((screen.width-width)/2)+",top="+((screen.height-height)/2));
		dialog.focus();
	}
	
	
	
	
	$(document).load(function() {
		// call initial function for SWF Loader
//$("#radioLive").attr("checked","checked");
		pageInit();
	});
	
	//
	//
	//
	//enable 'Cloud Cover' sub tab in PMOS
	//Jeon, Doe 2012-05-09
	//
	//
	//
	// View only SubTabs depending on which ModeTab has been selected
	function checkDisabledSubTabs(currentSubTab) {
		if ($("input:radio[name=rdo_mainTab]:checked").val()=="0") {	// 지점가이던스일 경우
			$("a[id^='a_subTabPoint_']").removeClass("dummy");	// 전체를 다 Enable
			$("a[id^='a_subTabPoint_']").removeClass("highlight");

			//doejeon meFine
			//doejeon meFine
			var meModelSelected = $("input:radio[name=rdo_subTabModePoint]:checked").val();
			var useVisibilityMenuInsteadOfPOP = (meModelSelected=="etc" || meModelSelected=="PYEONGCHANG")? true : false;
			var varNameStr = (useVisibilityMenuInsteadOfPOP)? " <strong>시정</strong>" : " <strong>강수확률</strong>"
			$("a[id='a_subTabPoint_2']").html(varNameStr);
			//doejeon meFine
			//doejeon meFine
			
			switch ($("input:radio[name=rdo_subTabModePoint]:checked").val()) {
				//=======================================doeJeon					
				//meBest
				// case "SBEST": // 
					// // $("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");
					// // $("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");
				// break;
				case "SBEST": 
//					$("#a_subTabPoint_1").addClass("dummy");
					$("#a_subTabPoint_2").addClass("dummy"); //20160325 best (excludes only pop)
//					$("#a_subTabPoint_3").addClass("dummy");
//					$("#a_subTabPoint_5").addClass("dummy");
//					$("#a_subTabPoint_6").addClass("dummy");
//					$("#a_subTabPoint_7").addClass("dummy");
//					if ((currentSubTab==0) || (currentSubTab==4)) {
					if (currentSubTab!=2) {	
						$("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");
						$("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");
					} else {
						mergedModel = false; 
						$("a[id^='rd_bt']").removeAttr("disabled");
						$("a[id^='a_subTabPointMode_']").removeAttr("disabled");
						$("#a_subTabPoint_8").addClass("highlight");
						$("#rdo_subTabPoint_8").attr("checked","checked");
					}
				break;
				case "MBEST": // ==gdaps
					$("#a_subTabPoint_2").addClass("dummy");
					$("#a_subTabPoint_4").addClass("dummy");
					$("#a_subTabPoint_6").addClass("dummy");
					if ((currentSubTab!=2)&&(currentSubTab!=4)&&(currentSubTab!=6)) {
						$("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");
						$("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");
					} else {
						mergedModel = false; 
						$("a[id^='rd_bt']").removeAttr("disabled");
						$("a[id^='a_subTabPointMode_']").removeAttr("disabled");
						$("#a_subTabPoint_8").addClass("highlight");
						$("#rdo_subTabPoint_8").attr("checked","checked");
					}
				break;
				//=======================================doeJeon	
				case "PMOS":
					$("#a_subTabPoint_1").addClass("dummy");
					//$("#a_subTabPoint_1").addClass("dummy");//change sj 20140120
//					$("#a_subTabPoint_7").addClass("dummy");

// 신적설 on					
//					if ((currentSubTab!=1)&&(currentSubTab!=3)&&(currentSubTab!=7)) {
//					if ((currentSubTab!=1)&&(currentSubTab!=7)) {
//alert(currentSubTab);
                    if (  currentSubTab!=1 ) {
						$("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");
						$("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");
					} else {
						//mergedModel = false; //add sj 20140106
						//$("a[id^='rd_bt']").removeAttr("disabled");//add sj 20140106
						//$("a[id^='a_subTabPointMode_']").removeAttr("disabled");//add sj 20140106
						if(mergedModel == true){//change sj 20140120
							$("#rd_bt0").attr("checked","checked");//해당데이터 없을시 PMOS 체크 add sj 20140106
							$("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");
							$("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");
							swfParam = getCurrentStatusForSwf();
							if (isFileThere(swfParam)==true) {
								sendMsgToActionScript(swfParam);
							} else {
								$("#rd_bt1").attr("checked","checked");//PMOS 없을시 RDPS 체크
								$("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");
								$("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");
								swfParam = getCurrentStatusForSwf();
								if (isFileThere(swfParam)==true) {
									sendMsgToActionScript(swfParam);
								} else {
									//$("#rd_bt2").attr("checked","checked");//RDPS도 없을시 KWRF 체크		
									//$("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");
									//$("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");									
									//swfParam = getCurrentStatusForSwf();
									//if (isFileThere(swfParam)==true) {
									//	sendMsgToActionScript(swfParam);
									//} else {
										$("#rd_bt3").attr("checked","checked");//KWRF도 없을시 ECMWF 체크			
										$("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");
										$("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");										
										swfParam = getCurrentStatusForSwf();
										if (isFileThere(swfParam)==true) {
											sendMsgToActionScript(swfParam);
										} else {
											alert('No data');
											$("a[id^='a_subTabPoint_']").removeClass("highlight");
											$("#a_subTabPoint_"+prevAIndex).addClass("highlight");
											$("#"+prevRadioIndex).attr("checked","checked");
										}
									//}
								}
							}
						}else {
							$("#a_subTabPoint_8").addClass("highlight");
							$("#rdo_subTabPoint_8").attr("checked","checked");
						}
					}
				break;
				case "RDPS": 
					$("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");
					$("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");
				break;
				case "KWRF":
					$("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");
					$("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");
				break;
				case "ECMW":
					//$("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");//change sj 20140120
					//$("#a_subTabPoint_2").addClass("dummy");//change sj 20140120
					//$("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");//change sj 20140120
					$("#a_subTabPoint_2").addClass("dummy");
					if (currentSubTab!=2) {
						$("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");
						$("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");
					} else {
					//mergedModel = false; //add sj 20140106
						//$("a[id^='rd_bt']").removeAttr("disabled");//add sj 20140106
						//$("a[id^='a_subTabPointMode_']").removeAttr("disabled");//add sj 20140106
						if(mergedModel == true){//change sj 20140120
							$("#rd_bt0").attr("checked","checked");//해당데이터 없을시 PMOS 체크 add sj 20140106
							$("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");
							$("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");
							swfParam = getCurrentStatusForSwf();
							if (isFileThere(swfParam)==true) {
								sendMsgToActionScript(swfParam);
							} else {
								$("#rd_bt1").attr("checked","checked");//PMOS 없을시 RDPS 체크
								$("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");
								$("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");
								swfParam = getCurrentStatusForSwf();
								if (isFileThere(swfParam)==true) {
									sendMsgToActionScript(swfParam);
								} else {
									//$("#rd_bt2").attr("checked","checked");//RDPS도 없을시 KWRF 체크		
									//$("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");
									//$("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");									
									//swfParam = getCurrentStatusForSwf();
									//if (isFileThere(swfParam)==true) {
									//	sendMsgToActionScript(swfParam);
									//} else {
										$("#rd_bt3").attr("checked","checked");//KWRF도 없을시 ECMWF 체크			
										$("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");
										$("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");										
										swfParam = getCurrentStatusForSwf();
										if (isFileThere(swfParam)==true) {
											sendMsgToActionScript(swfParam);
										} else {
											alert('No data');
											$("a[id^='a_subTabPoint_']").removeClass("highlight");
											$("#a_subTabPoint_"+prevAIndex).addClass("highlight");
											$("#"+prevRadioIndex).attr("checked","checked");
										}
									//}
								}
							}
						}else {
							$("#a_subTabPoint_8").addClass("highlight");
							$("#rdo_subTabPoint_8").attr("checked","checked");
						}
					}

				break;
              case "w_ECMW":
					//$("#a_subTabPoint_1").addClass("dummy");
					$("#a_subTabPoint_2").addClass("dummy");
					$("#a_subTabPoint_4").addClass("dummy");
					$("#a_subTabPoint_6").addClass("dummy");
					//$("#a_subTabPoint_7").addClass("dummy");
					                         
					if ((currentSubTab!=2)&&(currentSubTab!=4)&&(currentSubTab!=6)) {
					//if ((currentSubTab!=1)&&(currentSubTab!=2)&&(currentSubTab!=4)&&(currentSubTab!=6)&&(currentSubTab!=7)) {
						$("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");
						$("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");
                              //alert("GDPS selected !");
					} else {
						mergedModel = false; //add sj 20140106
						$("a[id^='rd_bt']").removeAttr("disabled");//add sj 20140106
						$("a[id^='a_subTabPointMode_']").removeAttr("disabled");//add sj 20140106
						$("#a_subTabPoint_8").addClass("highlight");
						$("#rdo_subTabPoint_8").attr("checked","checked");
					}
				break;
				case "EPSG":
					//$("#a_subTabPoint_1").addClass("dummy");
					$("#a_subTabPoint_2").addClass("dummy");
					$("#a_subTabPoint_4").addClass("dummy");
					$("#a_subTabPoint_6").addClass("dummy");
					$("#a_subTabPoint_7").addClass("dummy");//add 20140715 sj 신적설 비활성화
					                         
			       	//	if ((currentSubTab!=1)&&(currentSubTab!=4)&&(currentSubTab!=6)&&(currentSubTab!=7)) {
					//if ((currentSubTab!=1)&&(currentSubTab!=2)&&(currentSubTab!=4)&&(currentSubTab!=6)&&(currentSubTab!=7)) {
					if ((currentSubTab!=2)&&(currentSubTab!=4)&&(currentSubTab!=6)) {
						$("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");
						$("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");
                              //alert("GDPS selected !");
					} else {
						mergedModel = false; //add sj 20140106
						$("a[id^='rd_bt']").removeAttr("disabled");//add sj 20140106
						$("a[id^='a_subTabPointMode_']").removeAttr("disabled");//add sj 20140106
						$("#a_subTabPoint_8").addClass("highlight");
						$("#rdo_subTabPoint_8").attr("checked","checked");
					}
				break;
				case "GDPS" :
					//$("#a_subTabPoint_1").addClass("dummy");
					$("#a_subTabPoint_2").addClass("dummy");
					$("#a_subTabPoint_4").addClass("dummy");
					$("#a_subTabPoint_6").addClass("dummy");
					//$("#a_subTabPoint_7").addClass("dummy");
					                         
			       	//	if ((currentSubTab!=1)&&(currentSubTab!=4)&&(currentSubTab!=6)&&(currentSubTab!=7)) {
					//if ((currentSubTab!=1)&&(currentSubTab!=2)&&(currentSubTab!=4)&&(currentSubTab!=6)&&(currentSubTab!=7)) {
					if ((currentSubTab!=2)&&(currentSubTab!=4)&&(currentSubTab!=6)) {
						$("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");
						$("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");
                              //alert("GDPS selected !");
					} else {
						mergedModel = false; //add sj 20140106
						$("a[id^='rd_bt']").removeAttr("disabled");//add sj 20140106
						$("a[id^='a_subTabPointMode_']").removeAttr("disabled");//add sj 20140106
						$("#a_subTabPoint_8").addClass("highlight");
						$("#rdo_subTabPoint_8").attr("checked","checked");
					}
				break;
// John Hyun added
				case "UKPP":
					$("#a_subTabPoint_1").addClass("dummy");
					$("#a_subTabPoint_4").addClass("dummy");
					$("#a_subTabPoint_6").addClass("dummy");
					$("#a_subTabPoint_7").addClass("dummy");
					
					if ((currentSubTab!=1)&&(currentSubTab!=4)&&(currentSubTab!=6)&&(currentSubTab!=7)) {
						$("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");
						$("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");
					} else {
					//mergedModel = false; //add sj 20140106
						//$("a[id^='rd_bt']").removeAttr("disabled");//add sj 20140106
						//$("a[id^='a_subTabPointMode_']").removeAttr("disabled");//add sj 20140106
						if(mergedModel == true){
							
						}else {
							$("#a_subTabPoint_0").addClass("highlight");
							$("#rdo_subTabPoint_0").attr("checked","checked");
						}
					}
				break;
				case "PMOS2":
					$("#a_subTabPoint_1").addClass("dummy");
					$("#a_subTabPoint_2").addClass("dummy");
					$("#a_subTabPoint_3").addClass("dummy");
					$("#a_subTabPoint_4").addClass("dummy");
					$("#a_subTabPoint_5").addClass("dummy");
					$("#a_subTabPoint_6").addClass("dummy");
					$("#a_subTabPoint_7").addClass("dummy");
					
					if ((currentSubTab!=1)&&(currentSubTab!=4)&&(currentSubTab!=6)&&(currentSubTab!=7)) {
						$("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");
						$("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");
					} else {
						mergedModel = false; //add sj 20140106
						$("a[id^='rd_bt']").removeAttr("disabled");//add sj 20140106
						$("a[id^='a_subTabPointMode_']").removeAttr("disabled");//add sj 20140106
						$("#a_subTabPoint_0").addClass("highlight");
						$("#rdo_subTabPoint_0").attr("checked","checked");
					}
				break;		
				case "etc": //doejeon meFine
				
					//$("a[id='a_subTabPoint_2']").html(" <strong>시정</strong> ");//meFine
					
					//$("#a_subTabPoint_2").addClass("dummy");
					
					//if ((currentSubTab!=2)) {
						$("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");
						$("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");
					//} else {
					//	mergedModel = false; 
					//	$("a[id^='rd_bt']").removeAttr("disabled");
					//	$("a[id^='a_subTabPointMode_']").removeAttr("disabled");//add sj 20140106
					//	$("#a_subTabPoint_8").addClass("highlight");
					//	$("#rdo_subTabPoint_8").attr("checked","checked");
					//}
				break;		
				case "PYEONGCHANG": //doejeon meFine
				
					//$("a[id='a_subTabPoint_2']").html(" <strong>시정</strong> ");//meFine
				
					//$("#a_subTabPoint_2").addClass("dummy");
					
					//if ((currentSubTab!=2)) {
						$("a[id='a_subTabPoint_"+currentSubTab+"']").addClass("highlight");
						$("#rdo_subTabPoint_" + currentSubTab).attr("checked","checked");
					//} else {
					//	mergedModel = false; 
					//	$("a[id^='rd_bt']").removeAttr("disabled");
					//	$("a[id^='a_subTabPointMode_']").removeAttr("disabled");//add sj 20140106
					//	$("#a_subTabPoint_8").addClass("highlight");
					//	$("#rdo_subTabPoint_8").attr("checked","checked");
					//}
				break;		

// end of John Hyun adding part				
				}
		} else {													// 격자가이던스일 경우
//			$("a[id^='a_subTabLattice_']").addClass("dummy");	// 전체를 다 Disable
			$("a[id^='a_subTabLattice_']").removeClass("dummy");	// 전체를 다 Enable
			$("a[id='a_subTabLattice_"+currentSubTab+"']").addClass("highlight");


		}
		
			
	}
	
	// 격자가이던스용 이미지를 보여주는 함수
	function showImgForLattice() {
	
        var forecast_term =$("input:radio[name=chk_info_grid]:checked").attr("value");
		var imgDataType = $("input:radio[name=rdo_subTabLattice]:checked").attr("ref");
		var imgDataType1 = $("input:radio[name=rdo_subTabLattice]:checked").attr("ref1");
		var swfXmlType = $("input:radio[name=rdo_subTabModeLattice]:checked").attr("ref");
		var imgDateTime = $("#floatingMenuLattice option:selected").attr("ref");

		var frameIdx = (latticeImgIndex * 1);
		var frameNo;
		var result;
        var method_s;

        if ( (imgDataType1 =='pty') || (imgDataType1 =='pop') || (imgDataType1 == 'sky')) {
			method_s ="PPM/";
		 }else{
			method_s ="";
		 }
		//var imgHome = "/home/dfs/public_html/SHRT_R120/";
		var imgHome = "./SHRT_R120/";
		//var imgPath = imgHome + imgDataType + "/GIFD/";
		var imgPath = imgHome + imgDataType1;

		if ((frameIdx.toString()).length==1) {
			frameNo = "0" + frameIdx.toString();
		} else {
			frameNo = "" + frameIdx.toString();
		}

		var imgName = "";
		var imgParam = "<div style='text-align: center; margin: 0 auto;'>";

        if ( forecast_term !="Weekly" ) {
		  result = "";	// result 초기화;
		  imgName = "dfs_pmos_rdps_map_" + imgDataType1 + "_" + frameNo + "_" + imgDateTime.substr(0, 10) + "_s.png";	// 작은 이미지(PMOS)

		  result = $.ajax({
			url: "isFileExist.php",
			type: "GET",
			data: "fileName="+imgPath + "/" + imgName,
			async: false
		}).responseText;

		if (result=="exist") {
			imgParam += "<div style='float:left;margin-top:0;margin-left:150px;'><table><tr><th>PMOS</th></tr><tr><td><img src='" + imgPath + "/" + imgName + "' alt='" + imgName +"' id='img_lattice_click_" + imgName + "' style='cursor:pointer;' width='289' /></td></tr></table></div>";
				//alert( imgPath + "/" + imgName );			
		} else {
			imgParam += "<div style='float:left;margin:0;margin-left:150px;'><table><tr><th>PMOS</th></tr><tr><td><img src='./not_available1.jpg' style='cursor:pointer;' width='289' alt='not available' /></td></tr></table></div>";
		}

		result = "";	// result 초기화;
		imgName = "dfs_nppm_rdps_map_" + imgDataType1 + "_" + frameNo + "_" + imgDateTime.substr(0, 10) + "_s.png";	// 작은 이미지(RDPS)

		result = $.ajax({
			url: "isFileExist.php",
			type: "GET",
			data: "fileName="+imgPath + "/" + imgName,
			async: false
		}).responseText;
		
		if (result=="exist") {
			imgParam += "<div style='float:left;margin-top:0; margin-bottom:0; margin-left:10; margin-right:10;'><table><tr><th>"+method_s+"RDPS</th></tr><tr><td><img src='" + imgPath + "/" + imgName + "' alt='" + imgName +"' id='img_lattice_click_" + imgName + "' style='cursor:pointer;' width='289' /></td></tr></table></div>";
		} else {
			imgParam += "<div style='float:left;margin:0;'><table><tr><th>"+method_s+"RDPS</th></tr><tr><td><img src='./not_available1.jpg' style='cursor:pointer;' width='289' alt='not available' /></td></tr></table></div>";
		}

		result = "";	// result 초기화;
		imgName = "dfs_nppm_kwrf_map_" + imgDataType1 + "_" + frameNo + "_" + imgDateTime.substr(0, 10) + "_s.png";	// 작은 이미지(KWRF)

		//result = $.ajax({
		//	url: "isFileExist.php",
		//	type: "POST",
		//	data: "fileName="+imgPath + "/" + imgName,
		//	async: false
		//}).responseText;

		//if (result=="exist") {
		//	imgParam += "<div style='float:left;margin:0;'><table><tr><th>"+method_s+"KWRF</th></tr><tr><td><img src='" + imgPath + "/" + imgName + "' alt='" + imgName +"' id='img_lattice_click_" + imgName + "' style='cursor:pointer;' width='289' /></td></tr></table></div>";
		//} else {
		//	imgParam += "<div style='float:left;margin:0;'><table><tr><th>"+method_s+"KWRF</th></tr><tr><td><img src='./not_available1.jpg' style='cursor:pointer;' width='289' alt='not available' /></td></tr></table></div>";
		//}

		//imgParam += "<table ><tr><td><img src='./"+imgDataType1+"_legend.jpg' alt='' /></td></tr></table>";
		// ECMWF image display
		result = "";	// result 초기화;
		imgName = "dfs_nppm_ecmw_map_" + imgDataType1 + "_" + frameNo + "_" + imgDateTime.substr(0, 10) + "_s.png";	// 작은 이미지(KWRF)
		result = $.ajax({
			url: "isFileExist.php",
			type: "GET",
			data: "fileName="+imgPath + "/" + imgName,
			async: false
		}).responseText;

		if (result=="exist") {
			imgParam += "<div style='float:left;margin:0;'><table><tr><th>"+method_s+"ECMWF</th></tr><tr><td><img src='" + imgPath + "/" + imgName + "' alt='" + imgName +"' id='img_lattice_click_" + imgName + "' style='cursor:pointer;' width='289' /></td></tr></table></div>";
		} else {
			imgParam += "<div style='float:left;margin:0;'><table><tr><th>"+method_s+"ECMWF</th></tr><tr><td><img src='./not_available1.jpg' style='cursor:pointer;' width='289' alt='not available' /></td></tr></table></div>";
		}
// end of if then
}
else {
		result = "";	// result 초기화;
		imgName = "dfs_medm_gdps_map_" + imgDataType1 + "_" + frameNo + "_" + imgDateTime.substr(0, 10) + "_s.png";	// 작은 이미지(GDPS)
		result = $.ajax({
			url: "isFileExist.php",
			type: "GET",
			data: "fileName="+imgPath + "/" + imgName,
			async: false
		}).responseText;
		
		if (result=="exist") {
			imgParam += "<div align='center' style='float:center;'><table><tr><th>"+method_s+"GDPS</th></tr><tr><td><img src='" + imgPath + "/" + imgName + "' alt='" + imgName +"' id='img_lattice_click_" + imgName + "' style='cursor:pointer;' width='289' /></td></tr></table></div>";
		} else {
			imgParam += "<div align='center' style='float:center;margin:0;'><table><tr><th>"+method_s+"GDPS</th></tr><tr><td><img src='./not_available1.jpg' alt='not available' /></td></tr></table></div>";
		}

   }	
//doeJeon 0416, 2012		
//doeJeon 0416, 2012	
		if(imgDataType1!="tmp"){
			imgParam += "<table width='1200px' ><tr><td align='center' margin-left='-60'><img src='./"+imgDataType1+"_legend.jpg' alt='' /></td></tr></table>";
			imgParam += "</div><div style'='clear:both;'></div>";
		}else{
//			//alert(imgDataType1+imgDateTime.substr(4,2)+"_legend.png");
			imgParam += "<table width='1200px'><tr><td align='center'  margin-left='-60'><img src='./tmp_legend/"+imgDataType1+imgDateTime.substr(4,2)+"_legend.png' alt='' /></td></tr></table>";
			imgParam += "</div><div style'='clear:both;'></div>";
		}
//doeJeon 0416, 2012	
//doeJeon 0416, 2012	


//Paul S.H Lee      	
//		result = $.ajax({
//			url: "isFileExist.php",
//			type: "POST",
//			data: "fileName="+"./"+imgDataType1+"_legend.jpg",
//			async: false
//		}).responseText;
		
//		if (result=="exist") {
//						//imgParam += "<table width='820px' ><tr><td align='left' ><img src='./"+imgDataType1+"_legend.jpg' alt='' /></td></tr></table>";
//						imgParam += "<table width='900px'><tr><td align='center'><img src='./"+imgDataType1+"_legend.jpg' alt='' /></td></tr></table>";
//						imgParam += "</div><div style'='clear:both;'></div>";
//				}
//Paul S.H Lee
		

		$("#div_latticeImages").empty();
		$("#div_latticeImages").append(imgParam);
		//alert(imgName);
	}

	// 격자가이던스용 이미지의 시간 간격을 계산해서 배열로 만들어 'sliderSteps'에 세팅하는 함수
	function setTimeInterval() {
		var startTimeStr = $("#floatingMenuLattice option:selected").attr("ref");	// 2011 05 25 12 00
	
		var yearStr = startTimeStr.substr(0, 4);
		var monthStr = startTimeStr.substr(4, 2);
		var dayStr = startTimeStr.substr(6, 2);
		var hourStr = startTimeStr.substr(8, 2);
		var minStr = startTimeStr.substr(10, 2);
		
        var forecast_term =$("input:radio[name=chk_info_grid]:checked").attr("value");

		if ( forecast_term != "Weekly" ) {
			var dateFomatedTime = new Date(yearStr, (monthStr*1)-1, dayStr, (hourStr*1)+15, minStr);
		
			var tdStr = "";
			//for (var i=0; i<21; i++) {
			for (var i=0; i<28; i++) {	//change sujae 20130903. change lattice label count.
				var tempDateStr;
				var tempHourStr;
			
				if ((dateFomatedTime.getDate().toString()).length==1) {
					tempDateStr = "0" + dateFomatedTime.getDate();
				} else {
					tempDateStr = "" + dateFomatedTime.getDate();
				}
				if ((dateFomatedTime.getHours().toString()).length==1) {
					tempHourStr = "0" + dateFomatedTime.getHours();
				} else {
					tempHourStr = "" + dateFomatedTime.getHours();
				}
				tdStr += "<td height='25' align='center' class='labelStyle' id='td_latticeImageLabel_" + i + "'>" + tempDateStr + tempHourStr + "</td>";
				$("#td_latticeImage_"+i).attr("title", tempDateStr + tempHourStr);
				$("td[id^='td_latticeImage_']").removeClass("slider");//add 20141114 sj
				$("td[id^='td_latticeImage_']").addClass("slider");//add 20141114 sj
				sliderSteps[i] = tempDateStr + tempHourStr;
				
				dateFomatedTime.setHours(dateFomatedTime.getHours() + 3);
			}
			document.getElementById('td_latticeImage_20').style.display="table-cell";
                        //sliderSteps.length = 21;
                        sliderSteps.length = 28;	//change sujae 20130903. change lattice label count,
	            }
                else {
			var dateFomatedTime = new Date(yearStr, (monthStr*1)-1, dayStr, (hourStr*1)+12, minStr);
		
			var tdStr = "";
			for (var i=0; i<20; i++) {
				var tempDateStr;
				var tempHourStr;


				if ((dateFomatedTime.getDate().toString()).length==1) {
					tempDateStr = "0" + dateFomatedTime.getDate();
				} else {
					tempDateStr = "" + dateFomatedTime.getDate();
				}
				if ((dateFomatedTime.getHours().toString()).length==1) {
					tempHourStr = "0" + dateFomatedTime.getHours();
				} else {
					tempHourStr = "" + dateFomatedTime.getHours();
				}
				tdStr += "<td height='25' align='center' class='labelStyle' id='td_latticeImageLabel_" + i + "'>" + tempDateStr + tempHourStr + "</td>";
				$("#td_latticeImage_"+i).attr("title", tempDateStr + tempHourStr);
				sliderSteps[i] = tempDateStr + tempHourStr;
				
				dateFomatedTime.setHours(dateFomatedTime.getHours() + 12);		

			}
			document.getElementById('td_latticeImage_20').style.display="none";
                        sliderSteps.length = 20;
		    } 
 	
		$("#tr_imgStepsLabel").empty();
		$("#tr_imgStepsLabel").append(tdStr);
	}
	
	// idx에 맞는 슬라이드를 보여주는 함수
	function setSlideAt(idx) {
		$("td[id^='td_latticeImage_']").removeClass("sliderSelected");

		var frameIdx = (latticeImgIndex * 1);

		$("#td_latticeImage_"+frameIdx).addClass("sliderSelected");
		// 격자가이던스용 이미지 뿌려주기
		showImgForLattice();

	}
	
	// 화일이름 형식에 맞는 xml 데이터가 존재하는지 검사
/* 	function isFileThere(fileNameStr) {
		var subDir = "XML/";
		var tempStr = fileNameStr.split(" ");
		var tempDirSort = tempStr[0].split("_");
//alert(tempDirSort[0]+"/"+tempDirSort[1]+"/"+tempDirSort[2]+"/"+tempDirSort[3]+"/"+tempDirSort[4]);
		if ((tempDirSort[3]=="KWRF")&&(tempDirSort[4]=="NPPM")) {	// KWRF일 경우
			subDir += "KWRF/";
		} else if ((tempDirSort[3]=="RDPS")&&(tempDirSort[4]=="NPPM")) {	// RDPS일 경우
			subDir += "RDPS/";
        } else if ((tempDirSort[3]=="ECMW")&&(tempDirSort[4]=="NPPM")) {	// ECMWF일 경우
			subDir += "ECMWF/";
		} else if ((tempDirSort[3]=="BEST")) {	// doeJeon best
			////sysout(tempDirSort[3]);
			subDir += "BEST/";   
		} else if ((tempDirSort[3]=="RDPS")&&(tempDirSort[4]=="PMOS")) {	// PMOS일 경우
			subDir += "PMOS/";   
           	if (tempStr[1] =="SN3") {
         		tempStr[1]="S12"; // change S12 to SN6. 20130904 2C.Doe
         	}
		} else if (tempDirSort[1]=="MEDM") {
			if ((tempDirSort[4]=="PMOS")&&(tempDirSort[3]=="GDPS")){
        		subDir += "PMOS2/";
		    } else if ((tempDirSort[4]=="PMOS")&&(tempDirSort[3]=="EPSG")){
         		subDir += "ensembleMOS/"; 
            } else if (tempDirSort[3]=="GDPS"){
            	subDir += "GDPS/"; 
            }	
		} else if ((tempDirSort[3]=="SSPS")) {	// PMOS일 경우
			subDir += "SSPS/";   
		}

		if ( (tempDirSort[1]=="MEDM") && ( tempStr[1] =="MUL" ) ) {
			  var fileName = subDir + tempStr[0] + "_" + "MMX" + "." + tempStr[2] + ".xml";
          }else {		
			    var fileName = subDir + tempStr[0] + "_" + tempStr[1] + "." + tempStr[2] + ".xml";
          }
		
		// change for 신적설 PMOS 12 hour
		var fileName = subDir + tempStr[0] + "_" + tempStr[1] + "." + tempStr[2] + ".xml";   
		// //sysout(fileName);
//		////sysout('isFileThere():: param fileNameStr == ' + fileNameStr + " [isFileExist.php]" + fileName);      
		var result = $.ajax({
			url: "isFileExist.php",
			type: "GET",
			data: "fileName="+fileName,
			async: false
		}).responseText;
		if (result=="exist") {
			return true;
		}
          else {
                // John added for back to all graph and if there is no all chart then just stop               
                var bFlag =  $(':input:radio[name=rdo_subTabPoint]:checked').val();//요소
                if ( bFlag == 8 )     // change from 0 to 8 due to 8 종합 차트 기본
                { 
						isFirst = false;
						if(InitInfoUserStartPage == true) {
							//alert("데이터가 없습니다");
						} else {
							//alert("데이터가 없습니다");
						}
                //      cleanupSWFs();
                } else  { 
					isFirst = true; 
                }
                if (isFirst) {
	               if(mergedModel == true) {
				   
				   }else {//add sj 20131231 파일체크 없을시 예외처리 change sj 20140120
						//$("a[id^='a_subTabPoint_0']").removeClass("highlight");
						//$("a[id^='a_subTabPoint_1']").removeClass("highlight");
						//$("a[id^='a_subTabPoint_2']").removeClass("highlight");
						//$("a[id^='a_subTabPoint_3']").removeClass("highlight");
						//$("a[id^='a_subTabPoint_4']").removeClass("highlight");
						//$("a[id^='a_subTabPoint_5']").removeClass("highlight");
						//$("a[id^='a_subTabPoint_6']").removeClass("highlight");
						//$("a[id^='a_subTabPoint_7']").removeClass("highlight");
	               
						//$("#a_subTabPoint_8").addClass("highlight");
						//$("a[id^='a_subTabPoint_8']").click();
						//alert("데이터가 없습니다");//add 20140715 sj 
						return false;
				   }
                }
		return false;
	   }
	} */

	//SHRT.RDPS_MOS POP 201606160000 5 
	function isFileThere(fileNameStr) {
		var fileInfoArr = fileNameStr.split(' ');
		var key = fileInfoArr[0] + '.' +  fileInfoArr[1];
		var relativeFilePath = getTimeseriesXMLDataPropertyValue(key+'.REPO');
		relativeFilePath += getTimeseriesXMLDataPropertyValue(key+'.PRFX')
		relativeFilePath += '.' + fileInfoArr[2] + '.xml';
sysout("isFileThere():: [key]" + key + ".REPO_AND_PRFX  [relativeFilePath]" + relativeFilePath);      
		var result = $.ajax({
			url: "isFileExist.php",
			type: "GET",
			data: "fileName="+relativeFilePath,
			async: false
		}).responseText;
		if (result=="exist") {
			sysout('>>>'+relativeFilePath + 'is [EXIST]')
			return true;
		}
          else {
			sysout('>>>'+relativeFilePath + 'is [NOT EXIST!!!!!]')
              /*   // John added for back to all graph and if there is no all chart then just stop               
                var bFlag =  $(':input:radio[name=rdo_subTabPoint]:checked').val();//요소
                if ( bFlag == 8 ){ 
						isFirst = false;
						if(InitInfoUserStartPage == true) {
							//alert("데이터가 없습니다");
						} else {
							//alert("데이터가 없습니다");
						}
                //      cleanupSWFs();
                } else  { 
					isFirst = true; 
                }
                if (isFirst) {
	               if(mergedModel == true) {
				   
				   }else {//add sj 20131231 파일체크 없을시 예외처리 change sj 20140120
						//$("a[id^='a_subTabPoint_0']").removeClass("highlight");
						//$("a[id^='a_subTabPoint_1']").removeClass("highlight");
						//$("a[id^='a_subTabPoint_2']").removeClass("highlight");
						//$("a[id^='a_subTabPoint_3']").removeClass("highlight");
						//$("a[id^='a_subTabPoint_4']").removeClass("highlight");
						//$("a[id^='a_subTabPoint_5']").removeClass("highlight");
						//$("a[id^='a_subTabPoint_6']").removeClass("highlight");
						//$("a[id^='a_subTabPoint_7']").removeClass("highlight");
	               
						//$("#a_subTabPoint_8").addClass("highlight");
						//$("a[id^='a_subTabPoint_8']").click();
						//alert("데이터가 없습니다");//add 20140715 sj 
						return false;
				   }
                } */
		return false;
	   }
	}
	// 지점가이던스 날짜변경/지점변경시 호출작업	
var prevFloatingMenuPoint = 0;
var prevStndate = 0;
	function changeSelectPoint() {
				// swf 정보를 보냄
		var swfParam = getCurrentStatusForSwf();
        	if (isFileThere(swfParam)==true) {
			prevFloatingMenuPoint = $("#floatingMenuPoint option:selected").val();
			prevStndate = $("#stndate option:selected").val();
			sendMsgToActionScript(swfParam);
		} else {		// 원래대로 되돌린다
			$("#floatingMenuPoint").val(prevFloatingMenuPoint);
			$("#stndate").val(prevStndate);
		}
	}
	
	// 격자가이던스 날짜변경시 호출작업
	function changeSelectLattice() {
		// 격자가이던스용 이미지 뿌려주기
		setTimeInterval();
		showImgForLattice();
	}
	
	
	//meBest initialize
	$(function() {
		// 초기 서브메뉴를 선별하여 보여준다
		checkDisabledSubTabs(8);
                
		// 격자가이던스용 이미지 뿌려주기
		setTimeInterval();
		showImgForLattice();
		
		// 메인탭 (지점가이던스/격자가이던스) 클릭시 버튼 행동
		$("a[id^='a_mainTab_']").click(function() {
			var $column = $(this).attr("id").substr(10);
//$("#link_button").show();//add 20130613 sj
			$("a[id^='a_mainTab_']").removeClass("highlight");
			$(this).addClass("highlight");

			$("#td_radioMain_"+$column).find(":radio").attr("checked","checked");
			
			if ($column=="0") {	// 지점가이던스 선택시

				var currentSubTab = parseInt($("input:radio[name=rdo_subTabLattice]:checked").val());	// added to show same sub menu as Lattice guidence
				
				$("#div_latticeTopMenu, #div_latticeBody").removeClass("divShow").addClass("divHide");
				$("#div_pointTopMenu, #div_pointBody").removeClass("divHide").addClass("divShow");
				
				// 라디오 버튼을 선택한다.
				$("#td_radioTabPoint_"+currentSubTab).find(":radio").attr("checked","checked");
				
				// 서브메뉴를 선별하여 보여준다
				checkDisabledSubTabs(currentSubTab);
			
				// swf 정보를 보냄
				var swfParam = getCurrentStatusForSwf();
				sendMsgToActionScript(swfParam);
			} else if ($column=="1") {		// 격자가이던스 선택시
//$("#link_button").hide();//add 20130613 sj
				$("a[id^=a_subTabLattice_]").removeClass("highlight"); //지점가이던스 선택 후 격자 가이던스 선택 에러때문에 추가한 코드. -2012.01.18 soojin
				
				//var currentSubTab = parseInt($("input:radio[name=rdo_subTabPoint]:checked").val());	// added to show same sub menu as Point guidence
				var currentSubTab = parseInt($("input:radio[name=rdo_subTabPoint]:checked").val());
				if (currentSubTab==8) {		//지점메뉴 종합탭상태에서 격자메뉴로 넘어갈경우 격자의 서브메뉴가 기본적으로 기온이 선택되도록 수정 20130419 전수재
					currentSubTab = 0;
				}
				
				$("#div_pointTopMenu, #div_pointBody").removeClass("divShow").addClass("divHide");
				$("#div_latticeTopMenu, #div_latticeBody").removeClass("divHide").addClass("divShow");
				

				// 라디오 버튼을 클릭한다.
				$("#td_radioTabLattice_"+currentSubTab).find(":radio").attr("checked","checked");
			
				// 서브메뉴를 선별하여 보여준다
				checkDisabledSubTabs(currentSubTab);
							
				// 격자가이던스용 이미지 뿌려주기
				showImgForLattice();
			
			} else {												// 아무것도 아니면
				// do nothing
			}
		});
		
		// 서브탭 (지점가이던스용) 클릭시 버튼 행동
		$("a[id^='a_subTabPoint_']").click(function() {
			if ($(this).hasClass("dummy")!=true) {								// 비활성 상태가 아니라면(활성된 라디오라면)
				var prevRadioIndex = $("input:radio[name=rdo_subTabPoint]:checked").attr("id");	// 이전 선택된 상태의 라디오버튼 id를 가져온다
				var prevAIndex = prevRadioIndex.substr(16);	// 이전 선택된 상태의 a 태그의 id를 가져온다
				
				var $column = $(this).attr("ref");									// assign the ID of the column

				$("a[id^='a_subTabPoint_']").removeClass("highlight");
				$(this).addClass("highlight");
				$("#td_radioTabPoint_"+$column).find(":radio").attr("checked","checked");
				if($column == 8){//add sj 20140109
					mergedModel = false;
					$('#etc').attr('disabled', false);
				}
				if(mergedModel != true) {
					$("a[id^='rd_bt']").removeAttr("disabled");
					$("a[id^='a_subTabPointMode_']").removeAttr("disabled");
				}
				var beforeModelVal = $("input:radio[name=rdo_subTabModePoint]:checked").val();

				var model_seq;
				if(beforeModelVal=='PMOS'){
					model_seq = 0;
				}else if(beforeModelVal=='RDPS'){
					model_seq = 1;
				}else if(beforeModelVal=='KWRF'){
					model_seq = 2;
				}else if(beforeModelVal=='ECMW'){
					model_seq = 3;
				}else if(beforeModelVal=='GDPS'){ // GDPS	PMOS2->GDPS changed sj 20140103
					model_seq = 5;
				}else if(beforeModelVal=='w_ECMW'){ // 중기ECMWF
					model_seq = 6;
				}else if(beforeModelVal=='EPSG'){ // 중기ECMWF
					model_seq = 7;
				}else if (beforeModelVal == 'etc'){
					model_seq = 8;
				}else if(beforeModelVal=='SBEST'){
					model_seq = 9;
				}else if(beforeModelVal=='MBEST'){
					model_seq = 10;
				}
		
		
				if(mergedModel != true) {
					$("a[id^='a_subTabPointMode_"+model_seq+"']").addClass("highlight");	
				}
		
				// swf 정보를 보냄
				var swfParam = getCurrentStatusForSwf();
				if (isFileThere(swfParam)==true) {
					sendMsgToActionScript(swfParam);
				} else {		// 원상태로 되돌림
					if(model_seq < 4 || model_seq == 9){  // meBest model_seq == 9 appended  
//					if(mergedModel == true) {//add sj 20131231 모델합성장 데이터없는 요소 클릭시 예외처리 change sj 20140120
							$("#rd_bt0").attr("checked","checked");//해당데이터 없을시 PMOS 체크
							if(mergedModel != true){//add sj 20140120
								$("a[id^='a_subTabPointMode_']").removeClass("highlight");//add sj 20140120
								$("a[id^='a_subTabPointMode_0']").addClass("highlight");//add sj 20140120
							}
								swfParam = getCurrentStatusForSwf();
								if (isFileThere(swfParam)==true) {
	//							alert('test');
									sendMsgToActionScript(swfParam);
								} else {
	//							alert('test1');
								$("#rd_bt1").attr("checked","checked");//PMOS 없을시 RDPS 체크
								if(mergedModel != true){//add sj 20140120
									$("a[id^='a_subTabPointMode_']").removeClass("highlight");
									$("a[id^='a_subTabPointMode_1']").addClass("highlight");
								}
								swfParam = getCurrentStatusForSwf();
								if (isFileThere(swfParam)==true) {
	//							alert('test2');
									sendMsgToActionScript(swfParam);
								} else {
									//$("#rd_bt2").attr("checked","checked");//RDPS도 없을시 KWRF 체크	
									//if(mergedModel != true){//add sj 20140120
									//	$("a[id^='a_subTabPointMode_']").removeClass("highlight");
									//	$("a[id^='a_subTabPointMode_2']").addClass("highlight");								
									//}
									//swfParam = getCurrentStatusForSwf();
									//if (isFileThere(swfParam)==true) {
									//	sendMsgToActionScript(swfParam);
									//} else {
										$("#rd_bt3").attr("checked","checked");//KWRF도 없을시 ECMWF 체크
										if(mergedModel != true){//add sj 20140120
											$("a[id^='a_subTabPointMode_']").removeClass("highlight");									
											$("a[id^='a_subTabPointMode_3']").addClass("highlight");									
										}
										swfParam = getCurrentStatusForSwf();
										if (isFileThere(swfParam)==true) {
											sendMsgToActionScript(swfParam);
										} else {
											alert('No data');
											$("a[id^='a_subTabPoint_']").removeClass("highlight");
											$("#a_subTabPoint_"+prevAIndex).addClass("highlight");
											$("#"+prevRadioIndex).attr("checked","checked");
										}
									//}
								}
							}
						} else {//중기 파일 없을경우
							$("#rd_bt7").attr("checked","checked");//해당파일없을시 ENSEMBLE MOS체크
							if(mergedModel != true){
								$("a[id^='a_subTabPointMode_']").removeClass("highlight");
								$("a[id^='a_subTabPointMode_7']").addClass("highlight");
							}
								swfParam = getCurrentStatusForSwf();
								if (isFileThere(swfParam)==true) {
									sendMsgToActionScript(swfParam);
								} else {
								$("#rd_bt5").attr("checked","checked");//ENSEMBLE MOS 없을시 GDPS 체크
								if(mergedModel != true){//add sj 20140120
									$("a[id^='a_subTabPointMode_']").removeClass("highlight");
									$("a[id^='a_subTabPointMode_5']").addClass("highlight");
								}
								swfParam = getCurrentStatusForSwf();
								if (isFileThere(swfParam)==true) {
	//							alert('test2');
									sendMsgToActionScript(swfParam);
								} else {
									$("#rd_bt6").attr("checked","checked");//GDPS 없을시 ECMWF 체크
									if(mergedModel != true){//add sj 20140120
										$("a[id^='a_subTabPointMode_']").removeClass("highlight");
										$("a[id^='a_subTabPointMode_6']").addClass("highlight");								
									}
									swfParam = getCurrentStatusForSwf();
									if (isFileThere(swfParam)==true) {
										sendMsgToActionScript(swfParam);
									} else {
											alert('No data');
											$("a[id^='a_subTabPoint_']").removeClass("highlight");
											$("#a_subTabPoint_"+prevAIndex).addClass("highlight");
											$("#"+prevRadioIndex).attr("checked","checked");
										}
									}
								}
						}
					//} else {
						//$("a[id^='a_subTabPoint_']").removeClass("highlight");
						//$("#a_subTabPoint_"+prevAIndex).addClass("highlight");
						//$("#"+prevRadioIndex).attr("checked","checked");
					//}
				}
			} else {
				return false;
			}
		});	
		
		// 서브탭 (격자가이던스용) 클릭시 버튼 행동
		$("a[id^='a_subTabLattice_']").click(function() {
			if ($(this).hasClass("dummy")!=true) {								// 비활성 상태가 아니라면(활성된 라디오라면)
				var $column = $(this).attr("ref");									// assign the ID of the column

				$("a[id^='a_subTabLattice_']").removeClass("highlight");
				if(mergedModel != true){//add sj 20131231
					$(this).addClass("highlight");
				}
				$("#td_radioTabLattice_"+$column).find(":radio").attr("checked","checked");

				// 격자가이던스용 이미지 뿌려주기
				showImgForLattice();
			} else {
				return false;
			}
		});
		
		// 모드탭 (지점가이던스용) 클릭시 버튼 행동
		$("a[id^='a_subTabPointMode_']").click(function() {
			var $column = $(this).attr("ref");									// assign the ID of the column
			var $selected = $(this).val();
			//alert($column+"//"+$selected);
			var prev_val = $("input:radio[name=rdo_subTabModePoint]:checked").val();
			var prev_column = "a[id='a_subTabPointMode_0']";
			var prev_num = 0;
			//alert(prev_val + '/' + prev_column + '/' + prev_num);
			//alert($column+'/'+$selected);
			if(prev_val == 'PMOS') {
				prev_column = "a[id='a_subTabPointMode_0']";
				prev_num = 0;
			} else if (prev_val == 'RDPS') {
				prev_column = "a[id='a_subTabPointMode_1']";
				prev_num = 1;
			} else if (prev_val == 'KWRF') {
				prev_column = "a[id='a_subTabPointMode_2']";
				prev_num = 2;
			} else if (prev_val == 'ECMW') {
				prev_column = "a[id='a_subTabPointMode_3']";
				prev_num = 3;
			} else if (prev_val == 'GDPS') {
				prev_column = "a[id='a_subTabPointMode_5']";
				prev_num = 5;
			} else if (prev_val == 'w_ECMW') {
				prev_column = "a[id='a_subTabPointMode_6']";
				prev_num = 6;
			} else if (prev_val == 'EPSG') {
				prev_column = "a[id='a_subTabPointMode_7']";
				prev_num = 7;
			} else if (prev_val == 'etc') {
				prev_column = "a[id='a_subTabPointMode_8']";
				prev_num = 8;
			//doejeon=================================================
			} else if (prev_val == 'SBEST') {
				prev_column = "a[id='a_subTabPointMode_9']";
				prev_num = 9;
			}else if (prev_val == 'MBEST') {
				prev_column = "a[id='a_subTabPointMode_10']";
				prev_num = 10;
			} else if (prev_val == 'PYEONGCHANG') {
				prev_column = "a[id='a_subTabPointMode_11']";
				prev_num = 11;
				//InitstndateSelectOption("QueenYuna",'secondParamIsNotUsedLocally');  //doejeon
			}	
			//doejeon=================================================



			$("a[id^='a_subTabPointMode_']").removeClass("highlight");		//20130527 jeon su jae move to down(if).
			if(mergedModel != true){//add sj 20131231
				$(this).addClass("highlight");
			}
			$("#td_radioTabModePoint_"+$column).find(":radio").attr("checked","checked");
			
			
			var currentSubTab = parseInt($("input:radio[name=rdo_subTabPoint]:checked").val());
			// 서브메뉴를 선별하여 보여준다
			checkDisabledSubTabs(currentSubTab);
				
			//doejeon
			/**단기-중기-산악예보 가이던스 선택시에만 드랍다운 메뉴 바인딩했었는데..산악의 경우 서브메뉴간 지점리스트가 상이하기 때문에 서브탭클릭이벤트에 InitstndateSelectOption() 걸어줘*/
			if($("input:radio[name=rdo_subTabModePoint]:checked").val()=="PYEONGCHANG"){
				InitstndateSelectOption('QueenYuna','secondParamIsNotUsedLocally');
			}else if($("input:radio[name=rdo_subTabModePoint]:checked").val()=="etc"){
				InitstndateSelectOption('etc','secondParamIsNotUsedLocally');
			}
			//doejeon
			
			// swf 정보를 보냄
			var swfParam = getCurrentStatusForSwf();
			if (isFileThere(swfParam)==true) {
				sendMsgToActionScript(swfParam);
			} else {	// 화면에 아무것도 없다고 표시한다
				$("a[id^='a_subTabPointMode_']").removeClass("highlight");
				if(mergedModel != true){
					$(prev_column).addClass("highlight");
				}
				//$("#td_radioTabModePoint_"+$column).find(":radio").attr("checked","checked");
				$("#td_radioTabModePoint_"+prev_num).find(":radio").attr("checked","checked");
				checkDisabledSubTabs(8);
				var swfParam = getCurrentStatusForSwf();
				if (isFileThere(swfParam)==true) {
					sendMsgToActionScript(swfParam);
				}
			}
		});
		
		// 모드탭 (격자가이던스용) 클릭시 버튼 행동
		$("a[id^='a_subTabLatticeMode_']").click(function() {
			
			var $column = $(this).attr("ref");									// assign the ID of the column

			$("a[id^='a_subTabLatticeMode_']").removeClass("highlight");
			$(this).addClass("highlight");
			$("#td_radioTabModeLattice_"+$column).find(":radio").attr("checked","checked");
			
			var currentSubTab = parseInt($("input:radio[name=rdo_subTabLattice]:checked").val());
			
			// 서브메뉴를 선별하여 보여준다
			checkDisabledSubTabs(currentSubTab);

			// 격자가이던스용 이미지 뿌려주기
			showImgForLattice();
		});
		/*
		// 지점가이던스 날짜변경/지점변경시 호출작업	
		$("#floatingMenuPoint, #stndate").change(function() {
			var prevFloatingMenuPoint = $("#floatingMenuPoint option:selected").val();
			var prevStndate = $("#stndate option:selected").val();
		
			// swf 정보를 보냄
			var swfParam = getCurrentStatusForSwf();
			if (isFileThere(swfParam)==true) {
				sendMsgToActionScript(swfParam);
			} else {		// 원래대로 되돌린다
				$("#floatingMenuPoint").val(prevFloatingMenuPoint);
				$("#stndate").val(prevStndate);
			}
		});
		*/
		
		// 지점가이던스 Now 버튼 클릭시 호출작업
		$("#moveNowPagePoin").click(function() {
			var prevFloatingMenuPoint = $("#floatingMenuPoint option:selected").val();
			
			$("#floatingMenuPoint").val(14);
			
			// swf 정보를 보냄
			var swfParam = getCurrentStatusForSwf();
			if (isFileThere(swfParam)==true) {
				sendMsgToActionScript(swfParam);
			} else {		// 원래대로 되돌린다
				$("#floatingMenuPoint").val(prevFloatingMenuPoint);
			}			
		});
		
		// 지점가이던스 날짜이동버튼 클릭시 호출작업
		$("#movePagePoinP, #movePagePoinN").click(function() {
			var prevFloatingMenuPoint = $("#floatingMenuPoint option:selected").val();
			
			var cmdDirection =  $(this).attr("id").substr(12,1);
			var currentVal = $("#floatingMenuPoint option:selected").val() *1;
			
			if (cmdDirection=="P") {	// 이전 12시간 보기라면
				if (currentVal>0) {
					currentVal--;
					$("#floatingMenuPoint").val(currentVal);
				}
			} else {					// 이후 12시간 보기라면
				if (currentVal<14) {
					currentVal++;
					$("#floatingMenuPoint").val(currentVal);
				}
			}
			
			// swf 정보를 보냄
			var swfParam = getCurrentStatusForSwf();
                    //    alert(swfParam);
			if (isFileThere(swfParam)==true) {
				sendMsgToActionScript(swfParam);
			} else {		// 원래대로 되돌린다
				$("#floatingMenuPoint").val(prevFloatingMenuPoint);
			}
		});

		// 격자가이던스 Now 버튼 클릭시 호출작업
		$("#moveNowPageLatt").click(function() {
			$("#floatingMenuLattice").val(14);
			
			// 격자가이던스용 이미지 뿌려주기
			setTimeInterval();
			showImgForLattice();		
		});
		
		// 격자가이던스 날짜이동버튼 클릭시 호출작업
		$("#movePageLattP, #movePageLattN").click(function() {
			var cmdDirection =  $(this).attr("id").substr(12,1);
			var currentVal = $("#floatingMenuLattice option:selected").val() *1;
			
			if (cmdDirection=="P") {	// 이전 12시간 보기라면
				if (currentVal>0) {
					currentVal--;
					$("#floatingMenuLattice").val(currentVal);
				}
			} else {					// 이후 12시간 보기라면
				if (currentVal<14) {
					currentVal++;
					$("#floatingMenuLattice").val(currentVal);
				}
			}
			// 격자가이던스용 이미지 뿌려주기
			setTimeInterval();
			showImgForLattice();
		});
		
		// 플레이버튼 클릭시 호출작업
		$("#btn_latticePlay").click(function() {
			//var currentSlideIndex = 0;
			
			if ($(this).val()=="Play") {		// 플레이일 경우
				tempSlideIndex = latticeImgIndex;	// 지금의 슬라이드 이미지 번호를 기억한다.
				latticeImgIndex = 0;
				
				// 콘트롤 버튼들 모두 못움직이게 함
				//disableAllLatticeComponents();
			
				tid = setInterval(function () {
					if (latticeImgIndex>(sliderSteps.length-1)) {
						// 플레이가 끝나면 슬라이드를 원래대로 돌려놓고 setInterval 함수를 종료하는 부분
						latticeImgIndex = tempSlideIndex;
						setSlideAt(latticeImgIndex);
						
						$("#btn_latticePlay").val("Play");
						
						// 콘트롤 버튼을 모두 움직이도록 풀어줌
						//enableAllLatticeComponents();
						
						clearInterval(tid);
						//return;
					} else {
						setSlideAt(latticeImgIndex);
						latticeImgIndex++;
					}
				}, 1000);
			
				$("#btn_latticePlay").val("Stop");
			} else {							// 스톱일 경우
				clearInterval(tid);
				latticeImgIndex = tempSlideIndex;
				setSlideAt(latticeImgIndex);
				
				// 콘트롤 버튼을 모두 움직이도록 풀어줌
				//enableAllLatticeComponents();
			
				$(this).val("Play");
			}
		});
		
		// 슬라이드바를 직접 클릭할때 호출되는 함수
		$("td[id^='td_latticeImage_']").click(function() {
			var idx = $(this).attr("id").substr(16);

			latticeImgIndex = idx;
			
			setSlideAt(latticeImgIndex);
		});
		
		// 작은그림을 클릭하면 큰 그림을 보여준다
		$("img[id^='img_lattice_click_']").live("click", function() {
			//var imgDataType = $("input:radio[name=rdo_subTabLattice]:checked").attr("ref");
			var imgHome = "./SHRT_R120/";
			var imgDataType1 = $("input:radio[name=rdo_subTabLattice]:checked").attr("ref1");
			//var imgPath = imgHome + imgDataType + "/GIFD/";
			var imgPath = imgHome + imgDataType1;

			var $tempFileName = $(this).attr("id").substr(18);
			var $newFileName = $tempFileName.substring(0, $tempFileName.length-6) + ".png";

			var imgParam = "<p> 좌우 이동 : 화살표 키를 사용하세요 </p><img src='" + imgPath + "/" + $newFileName + "' alt='" + $newFileName +"' />";
			if  ((imgDataType1=="sky")||(imgDataType1=="pty")) {
				 imgParam += "<img src='./"+imgDataType1+".png' alt='"+imgDataType1 +"' />";
			}

			$("#dialog:ui-dialog").dialog("destroy");
			$("#div_lattice_image_full_size").dialog({
				height: 'auto',
				width: 'auto',
				modal: true,
				position: 'top',
				title: $newFileName,
				open: function(event, ui) {
					$(this).empty();
					$(this).html(imgParam);
				}

			});


			//alert($newFileName);
		});

		// Keyup Event
		$(document).keydown(function(e) {
			if ($("input:radio[name=rdo_mainTab]:checked").val()=="0") {	// 지점가이던스일 경우
				switch (e.which) {
					case 33:	// PgUp
						var prevFloatingMenuPoint = $("#floatingMenuPoint option:selected").val();
						
						var currentVal = $("#floatingMenuPoint option:selected").val() *1;
						
						if (currentVal>0) {
							currentVal--;
							$("#floatingMenuPoint").val(currentVal);
						}
						// swf 정보를 보냄
						var swfParam = getCurrentStatusForSwf();
						if (isFileThere(swfParam)==true) {
							sendMsgToActionScript(swfParam);
						} else {		// 원래대로 되돌린다.
							$("#floatingMenuPoint").val(prevFloatingMenuPoint);
						}
					break;
					case 34:	// PgDn
						var prevFloatingMenuPoint = $("#floatingMenuPoint option:selected").val();
						
						var currentVal = $("#floatingMenuPoint option:selected").val() *1;
						
						if (currentVal<14) {
							currentVal++;
							$("#floatingMenuPoint").val(currentVal);
						}
						// swf 정보를 보냄
						var swfParam = getCurrentStatusForSwf();
						if (isFileThere(swfParam)==true) {
							sendMsgToActionScript(swfParam);
						} else {		// 원래대로 되돌린다.
							$("#floatingMenuPoint").val(prevFloatingMenuPoint);
						}
					break;
					case 49:	// 1
					case 50:	// 2
					case 51:	// 3
					case 52:	// 4
					case 53:	// 5
					case 54:	// 6
					case 55:	// 7
					case 56:	// 8
						var index = e.which - 49;
						if ($("#a_subTabPoint_"+index).hasClass("dummy")!=true) {					// 비활성 상태가 아니라면(활성된 라디오라면)
							var prevRadioIndex = $("input:radio[name=rdo_subTabPoint]:checked").attr("id");	// 이전 선택된 상태의 라디오버튼 id를 가져온다
							var prevAIndex = prevRadioIndex.substr(16);	// 이전 선택된 상태의 a 태그의 id를 가져온다
							
							var $column = $("#a_subTabPoint_"+index).attr("ref");									// assign the ID of the column

							$("a[id^='a_subTabPoint_']").removeClass("highlight");
							$("#a_subTabPoint_"+index).addClass("highlight");
							$("#td_radioTabPoint_"+$column).find(":radio").attr("checked","checked");
								
							// swf 정보를 보냄
							var swfParam = getCurrentStatusForSwf();
							if (isFileThere(swfParam)==true) {
								sendMsgToActionScript(swfParam);
							} else {		// 원래대로 되돌린다
								$("a[id^='a_subTabPoint_']").removeClass("highlight");
								$("#a_subTabPoint_"+prevAIndex).addClass("highlight");
								$("#"+prevRadioIndex).attr("checked","checked");
							}
						}
					break;
					case 9:	// Tab
					break;
					case 38:	// up
					/*
						if ($("#a_subTabPoint_"+index).hasClass("dummy")!=true) {					// 비활성 상태가 아니라면(활성된 라디오라면)
							var currentValStndate = $("#stndate option:selected").val() *1;
							
							if (currentValStndate>0) {
								currentValStndate--;
								$("#stndate").val(currentValStndate);
							}
							// swf 정보를 보냄
							var swfParam = getCurrentStatusForSwf();
							if (isFileThere(swfParam)==true) {
								sendMsgToActionScript(swfParam);
							} else {		// 원상태로 되돌린다
							}
						}
					*/
					break;
					case 40:	// down
					/*
						if ($("#a_subTabPoint_"+index).hasClass("dummy")!=true) {					// 비활성 상태가 아니라면(활성된 라디오라면)
								var currentValStndate = $("#stndate option:selected").val() *1;
								
								if (currentValStndate<50) {
									currentValStndate++;
									$("#stndate").val(currentValStndate);
								}
							// swf 정보를 보냄
							var swfParam = getCurrentStatusForSwf();
							if (isFileThere(swfParam)==true) {
								sendMsgToActionScript(swfParam);
							} else {		// 원상태로 되돌린다
							}
						}
					*/
					break;
				}
			} else {													// 격자가이던스일 경우
				switch (e.which) {
					case 33:	// PgUp
						var currentVal = $("#floatingMenuLattice option:selected").val() *1;
						
						if (currentVal>0) {
							currentVal--;
							$("#floatingMenuLattice").val(currentVal);
						}
						// 격자가이던스용 이미지 뿌려주기
						setTimeInterval();
						showImgForLattice();
					break;
					case 34:	// PgDn
						var currentVal = $("#floatingMenuLattice option:selected").val() *1;
						
						if (currentVal<14) {
							currentVal++;
							$("#floatingMenuLattice").val(currentVal);
						}
						// 격자가이던스용 이미지 뿌려주기
						setTimeInterval();
						showImgForLattice();
					break;
					case 49:	// 1
					case 50:	// 2
					case 51:	// 3
					case 52:	// 4
					case 53:	// 5
					case 54:	// 6
					case 55:	// 7
					case 56:	// 8
						var index = e.which - 49;
						if ($("#a_subTabLattice_"+index).hasClass("dummy")!=true) {					// 비활성 상태가 아니라면(활성된 라디오라면)
							var $column = $("#a_subTabLattice_"+index).attr("ref");									// assign the ID of the column

							$("a[id^='a_subTabLattice_']").removeClass("highlight");
							$("#a_subTabLattice_"+index).addClass("highlight");
							$("#td_radioTabLattice_"+$column).find(":radio").attr("checked","checked");

							// 격자가이던스용 이미지 뿌려주기
							showImgForLattice();
						}
					break;
					case 9:	// Tab
					break;
					case 39:	// right
						if (latticeImgIndex< (sliderSteps.length-1)) {
							latticeImgIndex++;
                                                 // arrow key test
                                                 if ($("#div_lattice_image_full_size").dialog("isOpen") == true ) {                                                          
//                                                           $("#div_lattice_image_full_size").html('<img src="./SHRT_R120/tmp/dfs_nppm_rdps_map_tmp_06_2012101100.png" />') ;
                                                        var current = $("#div_lattice_image_full_size").dialog("option","title");
                                                        var cat1 = current.substr(current.length -21,3); 
                                                        var idx1 = current.substr(current.length -17, 2)*1 +1;
                                                       // alert(idx1);
   
                                                        if( idx1 < 10 ) {
                                                                         var idx2 = "0"+idx1;
                                                                        }
                                                        else {
                                                                      var idx2 = idx1+"";
                                                               }   
                                                        var title1 = current.substr(0,current.length-17)+idx2+current.substr(current.length-15,15); 
                                                        var url ="./SHRT_R120/"+cat1+"/"+title1 ;
                                                       
                                                        $("#div_lattice_image_full_size").html('<img src="'+url+'" />') ;
                                                        $("#div_lattice_image_full_size").dialog({ title: title1 });
                                                        }
                                                 // end of arrow test
                                                 
						} else {
							latticeImgIndex = 0;
                                                 if ($("#div_lattice_image_full_size").dialog("isOpen") == true ) {                                                          
                                                        var current = $("#div_lattice_image_full_size").dialog("option","title");
                                                        var cat1 = current.substr(current.length -21,3); 
                                                        var title1 = current.substr(0,current.length-17)+"00"+current.substr(current.length-15,15); 
                                                        var url ="./SHRT_R120/"+cat1+"/"+title1 ;
                                                        $("#div_lattice_image_full_size").html('<img src="'+url+'" />') ;
                                                        $("#div_lattice_image_full_size").dialog({ title: title1 });
                                                        } 

						}
						setSlideAt(latticeImgIndex);                                                 

					break;
					case 37:	// left
						if (latticeImgIndex>0) {
							latticeImgIndex--;
                                                // arrow key test
                                                 if ($("#div_lattice_image_full_size").dialog("isOpen") == true ) {                                                          
                                                        var current = $("#div_lattice_image_full_size").dialog("option","title");
                                                        var cat1 = current.substr(current.length -21,3); 
                                                        var idx1 = current.substr(current.length -17, 2)*1 -1; 
                                                        if( idx1 < 10 ) {
                                                                         var idx2 = "0"+idx1;
                                                                        }
                                                        else {
                                                                      var idx2 = idx1+"";
                                                               }   
                                                        var title1 = current.substr(0,current.length-17)+idx2+current.substr(current.length-15,15); 
                                                        var url ="./SHRT_R120/"+cat1+"/"+title1 ;
                                                       
                                                        $("#div_lattice_image_full_size").html('<img src="'+url+'" />') ;
                                                        $("#div_lattice_image_full_size").dialog({ title: title1 });
                                                        }

                                                 // end of arrow test
						} else {
							latticeImgIndex = sliderSteps.length -1;
                                                 if ($("#div_lattice_image_full_size").dialog("isOpen") == true ) {                                                          
                                                        var current = $("#div_lattice_image_full_size").dialog("option","title");
                                                        var cat1 = current.substr(current.length -21,3); 
                                                        var title1 = current.substr(0,current.length-17)+"20"+current.substr(current.length-15,15); 
                                                        var url ="./SHRT_R120/"+cat1+"/"+title1 ;
                                                        $("#div_lattice_image_full_size").html('<img src="'+url+'" />') ;
                                                        $("#div_lattice_image_full_size").dialog({ title: title1 });
                                                        } 
						}
						setSlideAt(latticeImgIndex);
					break;
				}
			}
			
			//alert(e.which);
		});
		
		// blur SWF Layer everytime it gots focus for body can capture key event, not SWF itself
		$("#SFS_meteogram.swf").focus(function () {
			//$(this).blur();
			alert("focused");
		});
	});
	

	
	
</script>
<!-- Show or Hide Model radio button : John Hyun -->
<script>
function Model_test(col) {
//alert(col);
	var prev = $("input:radio[name=rdo_subTabModePoint]:checked").val();
	var prev_num = 0;
	if (prev == "PMOS") {
		prev_num = 0;
	} else if (prev == "RDPS") {
		prev_num = 1;
	} else if (prev == "KWRF") {
		prev_num = 2;
	} else if (prev == "ECMW") {
		prev_num = 3;
	} else if (prev == "GDPS") {
		prev_num = 5;
	} else if (prev == "w_ECMW") {
		prev_num = 6;
	} else if (prev == "EPSG") {//20140702 Ensemble proc add jsj
		prev_num = 7;
	} else if (prev == "etc" ) {
		prev_num = 8;
	}else if (prev == "SBEST" ) {
		prev_num = 9;
	}else if (prev == "MBEST" ) {
		prev_num = 10;
	}
	$("#td_radioTabModePoint_"+col).find(":radio").attr("checked","checked");
	var currentSubTab = parseInt($("input:radio[name=rdo_subTabPoint]:checked").val());
	checkDisabledSubTabs(currentSubTab);
	var swfParam = getCurrentStatusForSwf();
	if (isFileThere(swfParam)==true) {
		return true;
	} else {
		$("#rdo_subTabPoint_"+currentSubTab).attr("checked","checked");
		$("#td_radioTabModePoint_"+prev_num).find(":radio").attr("checked","checked");
		return false;
	}
}


//modified on Oct 2015 by doejeon
// meBest showModel()
// res = Model_test(9); // doejeon
function showModel(value1 ){
        var col = 0;
        var res = false;
        if(value1 == "ShortTime") {//단기클릭했을경우
               InitstndateSelectOption(value1,$("input:radio[name=chk_info]:checked").val()) ;//20140701 jsj add
               var model_check = $("input:radio[name=rdo_subTabModePoint]:checked").val();
              if (model_check == 'w_ECMW') {
                     res = Model_test( 3) ;
                       if (res == true) {
                              document.getElementById('tbl_weekly').style.display ="none";
                              document.getElementById('tbl_etc').style.display ="none";
                              document.getElementById('tbl_modePoint').style.display ="";
                              $("a[id^='a_subTabPointMode_']" ).removeClass("highlight") ;
                                     if(mergedModel != true){ //add sj 20131231
                                           $("a[id^='a_subTabPointMode_3']" ).addClass("highlight") ;
                                   }
                             $( "a[id^='a_subTabPointMode_3']" ).click();
                      } else {                   
                              // res = Model_test(9); // doejeon
                            // if ( res == true) {
                                  // document.getElementById( 'tbl_weekly').style.display= "none";
                                   // document.getElementById('tbl_modePoint').style.display ="";
                                   // document.getElementById('tbl_etc').style.display ="none";
                                             // $( "a[id^='a_subTabPointMode_']").removeClass("highlight" );
                                   // if(mergedModel != true){
                                         // $( "a[id^='a_subTabPointMode_9']" ).addClass("highlight") ;
                                   // }
                                  // $( "a[id^='a_subTabPointMode_9']" ).click();
                              // }else{ 
                                  res = Model_test( 0) ; 
                                   if ( res == true) {
                                         document.getElementById( 'tbl_weekly').style.display= "none";
                                          document.getElementById('tbl_modePoint').style.display ="";
                                          document.getElementById('tbl_etc').style.display ="none";
                                                    $( "a[id^='a_subTabPointMode_']").removeClass("highlight" );
                                          if(mergedModel != true){
                                                $( "a[id^='a_subTabPointMode_0']" ).addClass("highlight") ;
                                          }
                                         $( "a[id^='a_subTabPointMode_0']" ).click();
                                   } else {
                                         res = Model_test(1) ;
                                          if ( res == true) {
                                                document.getElementById( 'tbl_weekly').style.display= "none";
                                                 document.getElementById('tbl_modePoint').style.display ="";
                                                 document.getElementById('tbl_etc').style.display ="none";
                                                 $("a[id^='a_subTabPointMode_']" ).removeClass("highlight") ;
                                                 if(mergedModel != true){
                                                       $( "a[id^='a_subTabPointMode_1']" ).addClass("highlight") ;
                                                 }
                                                $( "a[id^='a_subTabPointMode_1']" ).click();
                                          } else {
                                                res = Model_test( 2) ;
                                                 if ( res == true) {  
                                                       document.getElementById( 'tbl_weekly').style.display= "none";
                                                        document.getElementById('tbl_modePoint').style.display ="";
                                                        document.getElementById('tbl_etc').style.display ="none";
                                                        $("a[id^='a_subTabPointMode_']" ).removeClass("highlight") ;
                                                        if(mergedModel != true){
                                                              $( "a[id^='a_subTabPointMode_2']" ).addClass("highlight") ;
                                                        }
                                                       $( "a[id^='a_subTabPointMode_2']" ).click();
                                                 } else {
                                                       InitstndateSelectOption( "Weekly",$("input:radio[name=chk_info]:checked" ).val());
                                                        $("#medm" ).attr("checked" ,"checked" );
                                                        var currentSubTab = parseInt($("input:radio[name=rdo_subTabPoint]:checked").val()) ;
                                                        checkDisabledSubTabs(currentSubTab);
                                                        document.getElementById('tbl_etc').style.display ="none";
                                                        document.getElementById( 'tbl_weekly').style.display= "";
                                                        document.getElementById( 'tbl_modePoint').style.display= "none";
                                                        $("a[id^='a_subTabPointMode_']" ).removeClass("highlight") ;    
                                                         if(mergedModel != true){//add sj 20131231
                                                               $("a[id^='a_subTabPointMode_7']" ).addClass("highlight") ;
                                                        }
                                                       $( "a[id^='a_subTabPointMode_7']" ).click();
                                                 }
                                         }
                                  }
                           // }
                     }
              } else {
                     // res = Model_test( 9) ;
                      // if (res == true) {
                            // document.getElementById( 'tbl_weekly').style.display= "none";
                             // document.getElementById('tbl_etc').style.display ="none";
                             // document.getElementById('tbl_modePoint').style.display ="";
                             // $("a[id^='a_subTabPointMode_']" ).removeClass("highlight") ;
                                   // if(mergedModel != true){ //add sj 20131231
                                          // $("a[id^='a_subTabPointMode_9']" ).addClass("highlight") ;
                                   // }
                            // $( "a[id^='a_subTabPointMode_9']" ).click();
                                   // if(mergedModel == true) { //add sj 20140103
                                          // $("a[id^='a_subTabPoint_']" ).removeClass("dummy") ;
                                   // }
                      // } else {
                           
                           res = Model_test( 0) ; 
                            if ( res == true) {
                                  document.getElementById( 'tbl_weekly').style.display= "none";
                                   document.getElementById('tbl_modePoint').style.display ="";
                                   document.getElementById('tbl_etc').style.display ="none";
                                             $( "a[id^='a_subTabPointMode_']").removeClass("highlight" );
                                   if(mergedModel != true){
                                         $( "a[id^='a_subTabPointMode_0']" ).addClass("highlight") ;
                                   }
                                  $( "a[id^='a_subTabPointMode_0']" ).click();
                           
                            }else{
                           res = Model_test( 1) ;
                                   if ( res == true) {  
                                         document.getElementById( 'tbl_weekly').style.display= "";
                                          document.getElementById('tbl_modePoint').style.display ="none";
                                          document.getElementById('tbl_etc').style.display ="none";
                                      $( "a[id^='a_subTabPointMode_']").removeClass("highlight" );
                                          if(mergedModel != true){                        $( "a[id^='a_subTabPointMode_1']" ).addClass("highlight") ;
                                          }
                                         $( "a[id^='a_subTabPointMode_1']" ).click();
                                   } else {
                                         res = Model_test( 2) ;
                                          if ( res == true) {  
                                                document.getElementById( 'tbl_weekly').style.display= "";
                                                 document.getElementById('tbl_modePoint').style.display ="none";
                                                 document.getElementById('tbl_etc').style.display ="none";
                                                 $("a[id^='a_subTabPointMode_']" ).removeClass("highlight") ;
                                                 if(mergedModel != true){                                     $( "a[id^='a_subTabPointMode_2']" ).addClass("highlight") ;
                                                 }
                                                  $("a[id^='a_subTabPointMode_2']" ).click();
                                          } else {
                                                res = Model_test( 3) ;
                                                 if ( res == true) {  
                                                       document.getElementById( 'tbl_weekly').style.display= "";
                                                        document.getElementById('tbl_modePoint').style.display ="none";
                                                        document.getElementById('tbl_etc').style.display ="none";
                                                        $("a[id^='a_subTabPointMode_']" ).removeClass("highlight") ;
                                                        if(mergedModel != true){                                                       $( "a[id^='a_subTabPointMode_3']" ).addClass("highlight") ;
                                                        }
                                                       $( "a[id^='a_subTabPointMode_3']" ).click();
                                                 } else {
                                                       InitstndateSelectOption( "Weekly",$("input:radio[name=chk_info]:checked" ).val());
                                                        $("#medm" ).attr("checked" ,"checked" );
                                                        var currentSubTab = parseInt($("input:radio[name=rdo_subTabPoint]:checked").val()) ;
                                                        checkDisabledSubTabs(currentSubTab);
                                                        document.getElementById('tbl_etc').style.display ="none";
                                                        document.getElementById( 'tbl_weekly').style.display= "";
                                                        document.getElementById( 'tbl_modePoint').style.display= "none";
                                                        $("a[id^='a_subTabPointMode_']" ).removeClass("highlight") ;    
                                                        if(mergedModel != true){//add sj 20131231
                                                               $("a[id^='a_subTabPointMode_7']" ).addClass("highlight") ;
                                                        }
                                                       $( "a[id^='a_subTabPointMode_7']" ).click();
                                                 }
                                         }
                                  }
                                   /*$("#medm ").attr("checked","checked");
                                   var currentSubTab = parseInt($("input:radio[name=rdo_subTabPoint]:checked").val());
                                  checkDisabledSubTabs(currentSubTab);*/
                            }      
                   //  }
              }
       
       }
        if(value1 == "Weekly") {
              InitstndateSelectOption(value1,$( "input:radio[name=chk_info]:checked" ).val()); //20140701 jsj add
               var model_check = $("input:radio[name=rdo_subTabModePoint]:checked").val();
//alert('showModel model_check = '+model_check);
               if (model_check == 'ECMW') {
                     res = Model_test( 6) ;
                      if ( res == true) {
                            document.getElementById( 'tbl_weekly').style.display= "";
                             document.getElementById( 'tbl_modePoint').style.display= "none";
                             document.getElementById( 'tbl_etc').style.display= "none";
                             $("a[id^='a_subTabPointMode_']" ).removeClass("highlight") ;
                            if(mergedModel != true){ //add sj 20131231
                                    $("a[id^='a_subTabPointMode_6']" ).addClass("highlight") ;
                            }
                     $( "a[id^='a_subTabPointMode_6']" ).click();
                      } else {
                           // res = Model_test( 10) ;      //doejeon
                            // if ( res == true) {
                                  // document.getElementById( 'tbl_weekly').style.display= "none";
                                   // document.getElementById('tbl_modePoint').style.display ="";
                                   // document.getElementById('tbl_etc').style.display ="none";
                                             // $( "a[id^='a_subTabPointMode_']").removeClass("highlight" );
                                   // if(mergedModel != true){
                                         // $( "a[id^='a_subTabPointMode_10']" ).addClass("highlight") ;
                                   // }
                                  // $( "a[id^='a_subTabPointMode_10']" ).click();
                            // }else{
                                  res = Model_test( 7) ;
                                   if ( res == true) {  
                                         document.getElementById( 'tbl_weekly').style.display= "";
                                          document.getElementById('tbl_modePoint').style.display ="none";
                                          document.getElementById('tbl_etc').style.display ="none";
                                      $( "a[id^='a_subTabPointMode_']").removeClass("highlight" );
                                          if(mergedModel != true){                                      $( "a[id^='a_subTabPointMode_7']" ).addClass("highlight") ;
                                          }
                                         $( "a[id^='a_subTabPointMode_7']" ).click();
                                   } else {
                                         res = Model_test( 5) ;
                                          if ( res == true) {  
                                                document.getElementById( 'tbl_weekly').style.display= "";
                                                 document.getElementById('tbl_modePoint').style.display ="none";
                                                 document.getElementById('tbl_etc').style.display ="none";
                                                 $("a[id^='a_subTabPointMode_']" ).removeClass("highlight") ;
                                                 if(mergedModel != true){                                            $( "a[id^='a_subTabPointMode_5']" ).addClass("highlight") ;
                                                 }
                                                $( "a[id^='a_subTabPointMode_5']" ).click();
                                          } else {
                                                InitstndateSelectOption( "ShortTime",$("input:radio[name=chk_info]:checked" ).val());
                                                 $("#shrt" ).attr("checked" ,"checked" );
                                                 var currentSubTab = parseInt($("input:radio[name=rdo_subTabPoint]:checked").val()) ;
                                                 checkDisabledSubTabs(currentSubTab);
                                                 document.getElementById('tbl_etc').style.display ="none";
                                                 document.getElementById( 'tbl_weekly').style.display= "none";
                                                 document.getElementById( 'tbl_modePoint').style.display= "";
                                                 $("a[id^='a_subTabPointMode_']" ).removeClass("highlight") ;    
                                                 if(mergedModel != true){ //add sj 20131231
                                                        $("a[id^='a_subTabPointMode_0']" ).addClass("highlight") ;
                                                 }
                                                $( "a[id^='a_subTabPointMode_0']" ).click();
                                          }
                                  }
                                   /*$("#shrt ").attr("checked","checked");
                                   var currentSubTab = parseInt($("input:radio[name=rdo_subTabPoint]:checked").val());
                                  checkDisabledSubTabs(currentSubTab);*/
                            // } MEDM BEST      
                     }
              } else {
                     // res = Model_test( 10) ; //doejeon
                      // if ( res == true) {
                            // document.getElementById( 'tbl_weekly').style.display= "";
                             // document.getElementById( 'tbl_modePoint').style.display= "none";
                             // document.getElementById( 'tbl_etc').style.display= "none";
                      // $("a[id^='a_subTabPointMode_']" ).removeClass("highlight") ;
                            // if(mergedModel != true){
                                  // $( "a[id^='a_subTabPointMode_10']" ).addClass("highlight") ;
//                                   
                            // }
                           // $( "a[id^='a_subTabPointMode_10']" ).click();
                      // } else{
                     
                           res = Model_test( 7) ;
                            if ( res == true) {
                                  document.getElementById( 'tbl_weekly').style.display= "";
                                   document.getElementById( 'tbl_modePoint').style.display= "none";
                                   document.getElementById( 'tbl_etc').style.display= "none";
                                      $( "a[id^='a_subTabPointMode_']").removeClass("highlight" );
                                          if(mergedModel != true){ //add sj 20131231
                                                 $("a[id^='a_subTabPointMode_7']" ).addClass("highlight") ;
                                          }
                                         $( "a[id^='a_subTabPointMode_7']" ).click();
                            }else {
                                  res = Model_test( 5) ;
                                   if ( res == true) {  
                                         document.getElementById( 'tbl_weekly').style.display= "";
                                          document.getElementById('tbl_modePoint').style.display ="none";
                                          document.getElementById('tbl_etc').style.display ="none";
                                      $( "a[id^='a_subTabPointMode_']").removeClass("highlight" );
                                          if(mergedModel != true){                               $( "a[id^='a_subTabPointMode_5']" ).addClass("highlight") ;
                                          }
                                         $( "a[id^='a_subTabPointMode_5']" ).click();
                                   } else {
                                         res = Model_test( 6) ;
                                          if ( res == true) {  
                                                  document.getElementById('tbl_weekly').style.display ="";
                                                 document.getElementById('tbl_modePoint').style.display ="none";
                                                 document.getElementById('tbl_etc').style.display ="none";
                                                 $("a[id^='a_subTabPointMode_']" ).removeClass("highlight") ;
                                                 if(mergedModel != true){                                     $( "a[id^='a_subTabPointMode_6']" ).addClass("highlight") ;
                                                 }
                                                $( "a[id^='a_subTabPointMode_6']" ).click();
                                          } else {
                                                InitstndateSelectOption( "ShortTime",$("input:radio[name=chk_info]:checked" ).val());
                                                 $("#shrt" ).attr("checked" ,"checked" );
                                                 var currentSubTab = parseInt($("input:radio[name=rdo_subTabPoint]:checked").val()) ;
                                                 checkDisabledSubTabs(currentSubTab);
                                                 document.getElementById('tbl_etc').style.display ="none";
                                                  document.getElementById('tbl_weekly').style.display ="none";
                                                 document.getElementById( 'tbl_modePoint').style.display= "";
                                                 $("a[id^='a_subTabPointMode_']" ).removeClass("highlight") ;    
                                                 if(mergedModel != true){ //add sj 20131231
                                                        $("a[id^='a_subTabPointMode_0']" ).addClass("highlight") ;
                                                 }
                                                $( "a[id^='a_subTabPointMode_0']" ).click();                    
                                          }
                                  }
                           }      
                     // }//MEDM BEST
              }
       }
        if(value1 == "etc") {
              res = Model_test( 11) ; //doejeon  SSPS default submenu 8 --> 11
               if ( res == true) {
                     InitstndateSelectOption(value1,$( "input:radio[name=chk_info]:checked" ).val());
                      document.getElementById('tbl_etc').style.display ="";
                      document.getElementById('tbl_weekly').style.display ="none";
                      document.getElementById( 'tbl_modePoint').style.display= "none";
                      $("a[id^='a_subTabPointMode_']" ).removeClass("highlight") ;    
                      if(mergedModel != true){ //add sj 20131231
                             $("a[id^='a_subTabPointMode_11']" ).addClass("highlight") ;
                      }
                            $( "a[id^='a_subTabPointMode_11']" ).click();                   
               } else {
                     InitstndateSelectOption( "ShortTime",$("input:radio[name=chk_info]:checked" ).val());
                      $("#shrt" ).attr("checked" ,"checked" );
                      var currentSubTab = parseInt($("input:radio[name=rdo_subTabPoint]:checked").val()) ;
                       checkDisabledSubTabs(currentSubTab);
                       document.getElementById('tbl_etc').style.display ="none";
                     document.getElementById('tbl_weekly' ).style.display="none" ;
                       document.getElementById('tbl_modePoint').style.display ="";
                      $("a[id^='a_subTabPointMode_']" ).removeClass("highlight") ;    
                       if(mergedModel != true){ //add sj 20131231
                              $("a[id^='a_subTabPointMode_0']" ).addClass("highlight") ;
                      }

                              $("a[id^='a_subTabPointMode_0']" ).click();                    
               }
       }
}



function InitstndateSelectOption(value1, prev_rdo){//20140701 jsj add
	var stndate = document.getElementById('stndate');
	var preStnValue = stndate.options[stndate.selectedIndex].value;
	if ( preStnValue.length == 4 && preStnValue != '8318')
	{
		if ( preStnValue.substring(0,1) == '0'){
			preStnValue = preStnValue.substring(1,2);
		} else {
			preStnValue = preStnValue.substring(0,2);
		}
	}
	var selectIndex= 2;//디폴트 2번째 서울본청
//alert(prev_rdo);
//alert($("input:radio[name=chk_info]:checked").val());
	if(value1 == "ShortTime"){//단기
		var optionTextArray = ["주요도시 ","관심지역 ","수도권청(서울) ","수도권청(인천) ","수도권청(동두천) ","수도권청(파주) ","수도권청(수원) ","수도권청(이천) ","부산청(부산) ","부산청(울산) ",
		"부산청(통영) ","부산청(창원) ","부산청(진주) ","부산청(대구) ","부산청(울진) ","부산청(안동) ","부산청(상주) ","부산청(포항) ","부산청(구미) ","부산청(거창) ", "부산청(울릉도) ", "광주청(광주) ","광주청(목포) ","광주청(여수) ","광주청(완도) ","광주청(순천) ","광주청(전주) ","광주청(군산) ","광주청(정읍) ","광주청(남원) ","광주청(고창) ",
		"대전청(대전) ","대전청(서산) ","대전청(천안) ","대전청(보령) ","대전청(청주) ","대전청(충주) ","대전청(추풍령) ","강원청(북강릉) ","강원청(속초) ","강원청(동해) ",
		"강원청(철원) ","강원청(대관령) ","강원청(춘천) ","강원청(원주) ","강원청(영월) ","제주청(제주) ","제주청(서귀포) ","북한(평양) ","북한(선봉) ",
		"북한(수풍) ","북한(안주) ","북한(신계)"];
		var optionValueArray = ["8318","555","0","24","25","26","27","28","1","2",
		"3","4","5","6","7","8","9","10","11","12","38",
		"13","14","15","16","17","18","19","20","21","22",
		"23","29","30","31","32","33","34","35","36","37",
		"39","40","41","42","43","44","45","46","47",
		"48","49","50"];
		stndate.length = 0;
		for (var i=0; i < optionTextArray.length; i++) {
			stndate.add(new Option(optionTextArray[i], optionValueArray[i]));
			if(optionValueArray[i] == preStnValue) {
				selectIndex = i;
			}
		}
		stndate.selectedIndex = selectIndex;
	} else if(value1 == "Weekly") {//중기, 앙상블
		var optionTextArray = ["주요도시 ","관심지역 ","수도권청(서울) ","강원청(춘천) ","강원청(강릉)","대전청(대전) ","대전청(청주) ","광주청(광주) ","광주청(전주) ","부산청(부산) ","부산청(대구) ","제주청(제주) ","북한(평양) ","공항청(인천) ", "평창올림픽"];
		var optionValueArray = ["8318","555","0","41","35","23","32","13","18","1","6","44","46","100","9903"];
		stndate.length = 0;
		for (var i=0; i < optionTextArray.length; i++) {
			stndate.add(new Option(optionTextArray[i], optionValueArray[i]));
			if(optionValueArray[i] == preStnValue) {
				selectIndex = i;
			}
		}
		stndate.selectedIndex = selectIndex;
	} else {
		//METEOROLOGICAL SUPPORT TO THE PYEONCHANG 2018 OLYMPICS
		//doejeonFreeman
		if(value1=='QueenYuna'){
			stndate.length = 0;
			stndate.add(new Option('관심지역','555'));
			stndate.add(new Option('알펜시아(점프)','9901'));
			stndate.add(new Option('알펜시아(종합)','9902'));
			stndate.add(new Option('용평(알파인)','9903'));
			stndate.add(new Option('보광','9904'));
			stndate.add(new Option('정선(대회전)','9905'));
			
			stndate.selectedIndex = (preStnValue=='555')? 0: 1;
			return;
		}
		//doejeonFreeman
		
		var optionTextArray = ["주요지점 ","관심지역 ","수도권청(북한산) ","수도권청(마니산) ","부산청(가지산) ","부산청(화왕산) ","부산청(지리산) ","부산청(소백산) ","부산청(가야산) ","광주청(무등산) ","광주청(두륜산) ","광주청(덕유산) ","대전청(계룡산) ","대전청(월악산) ","강원청(설악산) ","강원청(오대산) ","강원청(치악산) ", "제주청(한라산) ", "고갯길(북부)","고갯길(중부)","고갯길(남부)"];
        //var optionValueArray = ["8318","555","3501","3502","3503","0001","0002","2301","2302","1301","1302","1303","0101","0102","0103","0104","0105","4401"];
		var optionValueArray = ["8318","555","0001","0002","0101","0102","0103","0104","0105","1301","1302","1303","2301","2302","3501","3502","3503","4401","8801","8802","8803"];
		stndate.length = 0;
		for (var i=0; i < optionTextArray.length; i++) {
			stndate.add(new Option(optionTextArray[i], optionValueArray[i]));
			if(preStnValue == '8318') {
				selectIndex = 0;
			} else if (preStnValue == '555') { 
				selectIndex = 1;
			} else if (preStnValue == '0') {
				selectIndex = 2;
			} else if (preStnValue == '1') {
				selectIndex = 4;
			} else {
				if(optionValueArray[i].substring(0,2) == preStnValue) {
					if(optionValueArray[i].substring(2,4) == '01') {
						selectIndex = i;
					}
				}
			}
		}
		stndate.selectedIndex = selectIndex;
	}
}

window.colnum = 1;

function showGrid(value1){
 if(value1 == "ShortTime") {
		window.colnum = 1;
		setTimeInterval();
		showImgForLattice();
        
 	}
 if(value1 == "Weekly") {
		window.colnum = 2;
		setTimeInterval();
		showImgForLattice();
	
	}

}

//window.onresize = resize;
function resize() {
/**
	var defaultHeaderLeftMargin = 88;
	var defaultBodyLeftMargin = 30;
	var browserWidth = document.body.clientWidth;
	var headerTable = document.getElementById('HeaderTable');
	var bodyChart = document.getElementById('div_Body');
	defaultHeaderLeftMargin = browserWidth - 1495;
	if(defaultHeaderLeftMargin < 5){
		defaultHeaderLeftMargin = 5;
	}
	defaultBodyLeftMargin = defaultHeaderLeftMargin - 55;
	headerTable.style.marginLeft = defaultHeaderLeftMargin+"px";
	bodyChart.style.marginLeft = defaultBodyLeftMargin+"px";
*/
}


</script>
</head>

<body onload="valueInit(); MainTabBorderInit(); SetLeftMargin();">
<script>
	function valueInit() {
		$("#radioBackup").attr("checked","checked");
	}
	function MainTabBorderInit() {
		$("#tbl_mainTab").removeClass("radioTableMain");//add 20141114 sj
		$("#tbl_mainTab").addClass("radioTableMain");//add 20141114 sj
	}
	function SetLeftMargin() {
		resize();
	}
</script>
<div id="outDiv" >	<!--add sujae 20130906-->
<div id="inDiv" style="width:1280px;">	<!--add sujae 20130906-->
<!--<table width="1050px" border="0" cellspacing="0" cellpadding="0" align="center" style="margin: 5px 95px 0 0;float:right;">-->
<table id="HeaderTable" width="1050px" border="0" cellspacing="0" cellpadding="0" align="center" style="margin: 5px 0 0 53px;float:left;">
  <tr>
    <td width="120" style="padding-top:2px;" valign="top">
<!--
	<table width="120" id="tbl_mainTab" class="radioTableMain" border="0" cellspacing="0" cellpadding="0">
        <tbody>
          <tr>
            <td align="center" id="td_radioMain_0" class="pg"><input class="rdo_mainTab" name="rdo_mainTab" value="0" type="radio" checked="checked" /><a href="#" id="a_mainTab_0" class="mainTab highlight" ref="pg"><strong>지점가이던스</strong></a></td>
          </tr>
          <tr>
            <td align="center" id="td_radioMain_1" class="lg"><input class="rdo_mainTab" name="rdo_mainTab" value="1" type="radio" /><a href="#" id="a_mainTab_1" class="mainTab" ref="lg"><strong>격자가이던스</strong></a></td>
          </tr>
        </tbody>
    </table>
-->

		<table width="120" id="tbl_mainTab" class="radioTableMain" border="0" cellspacing="0" cellpadding="0">
			<tbody>
				<tr>
					<td align="center" id="td_radioMain_0" class="pg"><input class="rdo_mainTab" name="rdo_mainTab" value="0" type="radio" checked="checked" /><a href="#" id="a_mainTab_0" class="mainTab highlight" ref="pg"><strong>지점예보</strong></a></td>
					<td align="center" id="td_radioMain_1" class="lg"><input class="rdo_mainTab" name="rdo_mainTab" value="1" type="radio" /><a href="#" id="a_mainTab_1" class="mainTab" ref="lg"><strong>격자예보</strong></a></td>
				</tr>

				<tr>
		  <!--
				<td align="center" id="td_radioMain_1" class="lg"><input class="rdo_mainTab" name="rdo_mainTab" value="1" type="radio" /><a href="#" id="a_mainTab_1" class="mainTab" ref="lg"><strong>격자가이던스</strong></a></td>
		  -->
					<td colspan="2" align="center" id="td_radioMain_2" class="hellg"><input class="rdo_mainTab" name="rdo_mainTab" value="2" type="radio" /><a href="http://172.20.134.32/MRFCST/index.php" id="a_mainTab_2" class="mainTab110" ref="hellg"><strong>중기예보문</strong></a></td>
				</tr>
		  
			</tbody>
		</table>

    </td>
    <td align="left" valign="top">
<div style="position:relative; top:0px; left:0px;" width="960">
    <div id="div_pointTopMenu" class="divShow">
      <table width="880" id="tbl_subTabPoint" class="radioTableSub" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
          <td width="103" id="td_radioTabPoint_8"><input class="rdo_subTab" id="rdo_subTabPoint_8" name="rdo_subTabPoint" value="8" type="radio" ref="MUL" checked="checked" /><a href="#" id="a_subTabPoint_8" class="subTab highlight" ref="8"> <strong>종합</strong></a></td>
          <td width="103" id="td_radioTabPoint_0"><input class="rdo_subTab" id="rdo_subTabPoint_0" name="rdo_subTabPoint" value="0" type="radio" ref="T3H" /><a href="#" id="a_subTabPoint_0" class="subTab" ref="0"> <strong>기온</strong></a></td>
          <td width="103" id="td_radioTabPoint_1"><input class="rdo_subTab" id="rdo_subTabPoint_1" name="rdo_subTabPoint" value="1" type="radio" ref="RN3"/><a href="#" id="a_subTabPoint_1" class="subTab" ref="1" > <strong>강수량</strong></a></td>
          <td width="103" id="td_radioTabPoint_2"><input class="rdo_subTab" id="rdo_subTabPoint_2" name="rdo_subTabPoint" value="2" type="radio" ref="POP" /><a href="#" id="a_subTabPoint_2" class="subTab" ref="2"> <strong>강수확률</strong></a></td>
          <td width="103" id="td_radioTabPoint_3"><input class="rdo_subTab" id="rdo_subTabPoint_3" name="rdo_subTabPoint" value="3" type="radio" ref="SKY" /><a href="#" id="a_subTabPoint_3" class="subTab" ref="3"> <strong>하늘상태</strong></a></td>
          <td width="103" id="td_radioTabPoint_4"><input class="rdo_subTab" id="rdo_subTabPoint_4" name="rdo_subTabPoint" value="4" type="radio" ref="REH" /><a href="#" id="a_subTabPoint_4" class="subTab" ref="4"> <strong>상대습도</strong></a></td>
          <td width="103" id="td_radioTabPoint_5"><input class="rdo_subTab" id="rdo_subTabPoint_5" name="rdo_subTabPoint" value="5" type="radio" ref="PTY" /><a href="#" id="a_subTabPoint_5" class="subTab" ref="5"> <strong>강수형태</strong></a></td>
          <td width="103" id="td_radioTabPoint_6"><input class="rdo_subTab" id="rdo_subTabPoint_6" name="rdo_subTabPoint" value="6" type="radio" ref="WSD" /><a href="#" id="a_subTabPoint_6" class="subTab" ref="6"> <strong>바람</strong></a></td>
          <td width="103" id="td_radioTabPoint_7"><input class="rdo_subTab" id="rdo_subTabPoint_7" name="rdo_subTabPoint" value="7" type="radio" ref="SN3" /><a href="#" id="a_subTabPoint_7" class="subTab" ref="7"> <strong>신적설</strong></a></td>
        </tr>
        
        <tr>
        
	<td colspan="9" valigh="bottom">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:3.5px;">
              <tr>
                <td>
					<input type="button" id="moveNowPagePoin" value="Now" style="width: 51px;">
	                <input type="button" id="movePagePoinP" value="-12h" style="width: 51px;">
	                <?php echo $quick_menu_div_point?>
	                <input type="button" id="movePagePoinN" value="+12h" style="margin-left:-2px;">
	                <select name="stndate" id="stndate" onChange="changeSelectPoint();" style="width:100px;float:center; " >
						<option value="8318">주요도시</option><!--add sujae 20130903-->
						<option value="555" >관심지역 </option>
						<option value="0" >수도권청(서울) </option>
		                <option value="24">수도권청(인천) </option>
		                <option value="25">수도권청(동두천) </option>
		                <option value="26">수도권청(파주) </option>
		                <option value="27">수도권청(수원) </option>
		                <option value="28">수도권청(이천) </option>
		                <option value="1">부산청(부산) </option>
		                <option value="2">부산청(울산) </option>
		                <option value="3">부산청(통영) </option>
		                <option value="4">부산청(창원) </option>
		                <option value="5">부산청(진주) </option>
		                <option value="6">부산청(대구) </option>
		                <option value="7">부산청(울진) </option>
		                <option value="8">부산청(안동) </option>
		                <option value="9">부산청(상주) </option>
		                <option value="10">부산청(포항) </option>
		                <option value="11">부산청(구미) </option>
		                <option value="12">부산청(거창) </option>
						<option value="38">부산청(울릉도) </option>
		                <option value="13">광주청(광주) </option>
		                <option value="14">광주청(목포) </option>
		                <option value="15">광주청(여수) </option>
		                <option value="16">광주청(완도) </option>
		                <option value="17">광주청(순천) </option>
		                <option value="18">광주청(전주) </option>
		                <option value="19">광주청(군산) </option>
		                <option value="20">광주청(정읍) </option>
		                <option value="21">광주청(남원) </option>
		                <option value="22">광주청(고창) </option>
		                <option value="23">대전청(대전) </option>
		                <option value="29">대전청(서산) </option>
		                <option value="30">대전청(천안) </option>
		                <option value="31">대전청(보령) </option>
		                <option value="32">대전청(청주) </option>
		                <option value="33">대전청(충주) </option>
		                <option value="34">대전청(추풍령) </option>
		                <option value="35">강원청(북강릉) </option>
		                <option value="36">강원청(속초) </option>
		                <option value="37">강원청(동해) </option>
		                <option value="39">강원청(철원) </option>
		                <option value="40">강원청(대관령) </option>
		                <option value="41">강원청(춘천) </option>
		                <option value="42">강원청(원주) </option>
		                <option value="43">강원청(영월) </option>
		                <option value="44">제주청(제주) </option>
		                <option value="45">제주청(서귀포) </option>
		                <option value="46">북한(평양) </option>
		                <option value="47">북한(선봉) </option>
		                <option value="48">북한(수풍) </option>
		                <option value="49">북한(안주) </option>
		                <option value="50">북한(신계) </option>
	                </select>                
	                
	           		<input type="radio" name="chk_info" id="shrt" value="ShortTime" class="radioButt" checked="checked" onclick="showModel(this.value);" >단기
					<input type="radio" name="chk_info" id="medm" value="Weekly" class="radioButt1" onclick="showModel(this.value);">중기
					<input type="radio" name="chk_info" id="etc" value="etc" class="radioButt1" onclick="showModel(this.value);">산악
					
	            </td>
                
				
					
				
                <td align="right">
                	
					<table width="180" id="tbl_modePoint"  class="radioTableMode" cellspacing="0" cellpadding="0">
	                	
	                    <tr>
	                        <td width="45" id="td_radioTabModePoint_0"><input id ="rd_bt0" class="rdo_subTabMode" name="rdo_subTabModePoint" value="PMOS" type="radio" ref="DFS_SHRT_STN_RDPS_PMOS" checked="checked" /> 
	                        	<a href="#" id="a_subTabPointMode_0" class="subTabMode_PMOS" ref="0"><strong>RDPS<br>MOS</strong></a>
                        	</td>
	                        <td width="45" id="td_radioTabModePoint_1"><input id ="rd_bt1" class="rdo_subTabMode" name="rdo_subTabModePoint" value="RDPS" type="radio" ref="DFS_SHRT_STN_RDPS_NPPM" />
	                        	 <a href="#" id="a_subTabPointMode_1" class="subTabMode" ref="1"><strong>RDPS</strong></a>
                        	</td>
							<td width="45" id="td_radioTabModePoint_3"><input id ="rd_bt3" class="rdo_subTabMode" name="rdo_subTabModePoint" value="ECMW" type="radio" ref="DFS_SHRT_STN_ECMW_NPPM" /> 
	                        	<a href="#" id="a_subTabPointMode_3" class="subTabMode_ECMWF" ref="3"><strong>ECMWF<br>MOS</strong></a>
                        	</td>
	                    	<!--doeJeon meBest-->
	                        <td width="45" id="td_radioTabModePoint_9"><input id ="rd_bt9" class="rdo_subTabMode" name="rdo_subTabModePoint" value="SBEST" type="radio" ref="DFS_SHRT_STN_BEST_MERG"  /> 
	                        	<a href="#" id="a_subTabPointMode_9" class="subTabMode" ref="9"><strong>BEST</strong></a>
                        	</td>
	                    	<!--doeJeon-->
	                    </tr>
	                </table>                               	

 
	                <table width="180" id="tbl_weekly"  style="display:none;" class="radioTableMode" cellspacing="0" cellpadding="0">
	                    <tr border="0">
	                    	<!--doeJeon meBest
	                        <td width="45" id="td_radioTabModePoint_10"><input id ="rd_bt10" class="rdo_subTabMode" name="rdo_subTabModePoint" value="MBEST" type="radio" ref="DFS_MEDM_STN_BEST_MERG"/> 
	                        	<a href="#" id="a_subTabPointMode_10" class="subTabMode" ref="10"><strong>Best</strong></a>
                        	</td>
	                    		-->
	                    	<!--doeJeon-->
							<td width="60" id="td_radioTabModePoint_7"><input id="rd_bt7" class="rdo_subTabMode" name="rdo_subTabModePoint" value="EPSG" type="radio" ref="DFS_MEDM_STN_EPSG_PMOS" /> 
								 <a href="#" id="a_subTabPointMode_7" class="subTabMode_ENSEMBLE" ref="7"><strong>EPSG<br>MOS</strong></a></td>
							<td width="60" id="td_radioTabModePoint_5"><input id="rd_bt5" class="rdo_subTabMode" name="rdo_subTabModePoint" value="GDPS" type="radio" ref="DFS_MEDM_STN_GDPS_NPPM" /> 
								 <a href="#" id="a_subTabPointMode_5" class="subTabMode_GDPS" ref="5"><strong>GDAPS<br>MOS</strong></a></td>
		         			 <td width="60" id="td_radioTabModePoint_6"><input id="rd_bt6" class="rdo_subTabMode" name="rdo_subTabModePoint" value="w_ECMW" type="radio" ref="DFS_MEDM_STN_ECMW_NPPM" /> 
								 <a href="#" id="a_subTabPointMode_6" class="subTabMode_ECMWF_MEDM" ref="6"><strong>ECMWF<br>MOS</strong></a></td>
	                    </tr>
	                </table>                        
	                
					<table width="160" id="tbl_etc"  style="display:none;" class="radioTableMode" cellspacing="0" cellpadding="0">	
	           			<tr border="0">
						 	<!--doejeon-->
	               			<td width="80" id="td_radioTabModePoint_11"><input id="rd_bt11" class="rdo_subTabMode" name="rdo_subTabModePoint" value="PYEONGCHANG" type="radio" ref="DFS_SHRT_STN_SSPS" /> 
						 		<a href="#" id="a_subTabPointMode_11" class="subTabMode_SSPS" ref="11"><strong>평창올림픽</strong></a>
						 	</td>
						 	<!--doejeon-->
	               			<td width="80" id="td_radioTabModePoint_8"><input id="rd_bt8" class="rdo_subTabMode" name="rdo_subTabModePoint" value="etc" type="radio" ref="DFS_SHRT_STN_SSPS" /> 
							 	<a href="#" id="a_subTabPointMode_8" class="subTabMode_SSPS" ref="8"><strong>산악예보</strong></a>
						 	</td>
		           		</tr>
	                </table>                        
	                
				</td>
            </tr>
            </table>          </td>
          </tr>
      </table>
    </div>
    
    <div id="div_latticeTopMenu" class="divHide">
      <table width="880" id="tbl_subTabLattice" class="radioTableSub" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
          <td width="110" id="td_radioTabLattice_0"><input class="rdo_subTab" id="rdo_subTabLattice_0" name="rdo_subTabLattice" value="0" type="radio" ref1="tmp" ref="TMP" checked/><a href="#" id="a_subTabLattice_0" class="subTab" ref="0"><strong>기온</strong></a></td>
          <td width="110" id="td_radioTabLattice_1"><input class="rdo_subTab" id="rdo_subTabLattice_1" name="rdo_subTabLattice" value="1" type="radio" ref1="rn3" ref="RN3" /><a href="#" id="a_subTabLattice_1" class="subTab" ref="1"><strong>강수량</strong></a></td>
          <td width="110" id="td_radioTabLattice_2"><input class="rdo_subTab" id="rdo_subTabLattice_2" name="rdo_subTabLattice" value="2" type="radio" ref1="pop" ref="POP" /><a href="#" id="a_subTabLattice_2" class="subTab" ref="2"><strong>강수확률</strong></a></td>
          <td width="110" id="td_radioTabLattice_3"><input class="rdo_subTab" id="rdo_subTabLattice_3" name="rdo_subTabLattice" value="3" type="radio" ref1="sky" ref="SKY" /><a href="#" id="a_subTabLattice_3" class="subTab" ref="3"><strong>하늘상태</strong></a></td>
          <td width="110" id="td_radioTabLattice_4"><input class="rdo_subTab" id="rdo_subTabLattice_4" name="rdo_subTabLattice" value="4" type="radio" ref1="reh" ref="REH" /><a href="#" id="a_subTabLattice_4" class="subTab" ref="4"><strong>상대습도</strong></a></td>
          <td width="110" id="td_radioTabLattice_5"><input class="rdo_subTab" id="rdo_subTabLattice_5" name="rdo_subTabLattice" value="5" type="radio" ref1="pty" ref="GPT_MASK" /><a href="#" id="a_subTabLattice_5" class="subTab" ref="5"><strong>강수형태</strong></a></td>
          <td width="110" id="td_radioTabLattice_6"><input class="rdo_subTab" id="rdo_subTabLattice_6" name="rdo_subTabLattice" value="6" type="radio" ref1="uvw" ref="UVW" /><a href="#" id="a_subTabLattice_6" class="subTab" ref="6"><strong>바람</strong></a></td>
          <td width="110" id="td_radioTabLattice_7"><input class="rdo_subTab" id="rdo_subTabLattice_7" name="rdo_subTabLattice" value="7" type="radio" ref1="sn3" ref="SN3" /><a href="#" id="a_subTabLattice_7" class="subTab" ref="7"><strong>신적설</strong></a></td>
        </tr>
        <tr>
          <td colspan="8" valigh="bottom">

            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:3.7px;">
              <tr>
                <td>
			<input type="button" id="moveNowPageLatt" value="Now" style="width:51px;"/>
                	<input type="button" id="movePageLattP" value="-12h" style="width:51px;"/>
                	<?php echo $quick_menu_div_lattice?>
               	 	<input type="button" id="movePageLattN" value="+12h" style="margin-left:-2px;"/>           

            		<input type="radio" name="chk_info_grid" value="ShortTime" class="radioButt" checked="checked" onclick="showGrid(this.value);" > 단기
<!--			<input type="radio" name="chk_info_grid" value="Weekly" class="radioButt" onclick="showGrid(this.value);"> 주간
 		 -->
		</td>
                <td align="right">&nbsp;
		<!--
			  <table width="120" id="tbl_modeLattice" class="radioTableMode" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td width="60" id="td_radioTabModeLattice_0"><input class="rdo_subTabMode" name="rdo_subTabModeLattice" value="PMOS" ref="pmos_rdps" type="radio" checked="checked" /><a href="#" id="a_subTabLatticeMode_0" class="subTabMode highlight" ref="0"><strong>PMOS</strong></a></td>
                        <td width="60" id="td_radioTabModeLattice_1"><input class="rdo_subTabMode" name="rdo_subTabModeLattice" value="RDPS" ref="nppm_rdps" type="radio" /><a href="#" id="a_subTabLatticeMode_1" class="subTabMode" ref="1"><strong>RDPS</strong></a></td>
                        <td width="60" id="td_radioTabModeLattice_2"><input class="rdo_subTabMode" name="rdo_subTabModeLattice" value="KWRF" ref="nppm_kwrf" type="radio" /><a href="#" id="a_subTabLatticeMode_2" class="subTabMode" ref="2"><strong>KWRF</strong></a></td>
                    </tr>
                </table>                
				-->
				</td>
              </tr>
            </table>          </td>
        </tr>
      </table>
    </div>
</div>
    </td>
	<td style="width:90px;">
		<table style="width:90px; color:white; font-size:0.75em;font-family:Dotum;background-color:#677AFB;" class="radioPageChange">
			<tr>
				<td>
					<input type="radio" name="radioPageChange" id="radioLive" style="margin-left:-8px;margin-top:-5px;vertical-align:-2px;" onclick="window.location.href='http://172.20.134.31/sfs_main.php';">현업
				</td>
			</tr>
			<tr>
				<td>
					<input type="radio" name="radioPageChange" id="radioBackup" style="margin-left:-8px;margin-top:-5px;vertical-align:-2px;" onclick="window.location.href='http://172.20.134.32/sfs_main.php';" checked="checked" >백업
				</td>
			</tr>
			<tr>
				<td style="padding-top:3px;">
					<div id="link_monitoring"><button id="link_button" onclick="window.open('http://172.20.134.32/TownMonitoring/town_main.html','_self')" style="width:60px;font-family:Dotum;border: 0px solid black;color:white;font-size:1em;margin-left:-2px;margin-top:-5px;margin-bottom:-3px;line-height:1.5em;background-color:#677AFB;z-index:11;">모니터링</button></div>
				</td>
			</tr>
		</table>
	</td>
  </tr>
</table>
<!--<div id="link_monitoring" style="position:absolute;left:50%;width:515px;z-index:11;"><button id="link_button" onclick="window.open('http://172.20.134.32/TownMonitoring/town_main.html','_self')" style="float:right;width:80px;height:30px;border: 0px solid black;text-align:center;font-size:0.6em;line-height:1.5em;background-color:#FFFFFF;z-index:11;">모니터링</button></di-->
<!--<div id="div_Body" style="margin:0 50px 0 0;float:right;">-->
<div id="div_Body" style="float:left;"> 

    <div id="div_pointBody" class="divShow mainBodyPoint">
    <?php include "sfs_point.php" ?>
    </div>
    <div id="div_latticeBody" class="divHide mainBodyLattice" style="margin:0 auto; padding:0;">
    <?php include "sfs_lattice.php" ?>
    </div>
</div>
</div>	<!--inDiv-->
</div>	<!--outDiv-->
</body>
</html>

	
