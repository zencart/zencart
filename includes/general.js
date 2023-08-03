//
// +----------------------------------------------------------------------+
// |zen-cart Open Source E-commerce                                       |
// +----------------------------------------------------------------------+
// | Copyright (c) 2003 The zen-cart developers                           |
// |                                                                      |   
// | http://www.zen-cart.com/index.php                                    |   
// |                                                                      |   
// | Portions Copyright (c) 2003 osCommerce                               |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the GPL license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at the following url:           |
// | http://www.zen-cart.com/license/2_0.txt.                             |
// | If you did not receive a copy of the zen-cart license and are unable |
// | to obtain it through the world-wide-web, please send a note to       |
// | license@zen-cart.com so we can mail you a copy immediately.          |
// +----------------------------------------------------------------------+
// $Id: general.js 1105 2005-04-04 22:05:35Z birdbrain $
//

function SetFocus(TargetFormName) {
  var target = 0;
  if (TargetFormName != "") {
    for (i=0; i<document.forms.length; i++) {
      if (document.forms[i].name == TargetFormName) {
        target = i;
        break;
      }
    }
  }

  var TargetForm = document.forms[target];
    
  for (i=0; i<TargetForm.length; i++) {
    if ( (TargetForm.elements[i].type != "image") && (TargetForm.elements[i].type != "hidden") && (TargetForm.elements[i].type != "reset") && (TargetForm.elements[i].type != "submit") ) {
      TargetForm.elements[i].focus();

      if ( (TargetForm.elements[i].type == "text") || (TargetForm.elements[i].type == "password") ) {
        TargetForm.elements[i].select();
      }

      break;
    }
  }
}

function RemoveFormatString(TargetElement, FormatString) {
  if (TargetElement.value == FormatString) {
    TargetElement.value = "";
  }

  TargetElement.select();
}

function CheckDateRange(from, to) {
  if (Date.parse(from.value) <= Date.parse(to.value)) {
    return true;
  } else {
    return false;
  }
}

function IsValidDate(DateToCheck, FormatString) {
  var strDateToCheck;
  var strDateToCheckArray;
  var strFormatArray;
  var strFormatString;
  var strDay;
  var strMonth;
  var strYear;
  var intday;
  var intMonth;
  var intYear;
  var intDateSeparatorIdx = -1;
  var intFormatSeparatorIdx = -1;
  var strSeparatorArray = new Array("-"," ","/",".");
  var strMonthArray = new Array("jan","feb","mar","apr","may","jun","jul","aug","sep","oct","nov","dec");
  var intDaysArray = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

  strDateToCheck = DateToCheck.toLowerCase();
  strFormatString = FormatString.toLowerCase();
  
  if (strDateToCheck.length != strFormatString.length) {
    return false;
  }

  for (i=0; i<strSeparatorArray.length; i++) {
    if (strFormatString.indexOf(strSeparatorArray[i]) != -1) {
      intFormatSeparatorIdx = i;
      break;
    }
  }

  for (i=0; i<strSeparatorArray.length; i++) {
    if (strDateToCheck.indexOf(strSeparatorArray[i]) != -1) {
      intDateSeparatorIdx = i;
      break;
    }
  }

  if (intDateSeparatorIdx != intFormatSeparatorIdx) {
    return false;
  }

  if (intDateSeparatorIdx != -1) {
    strFormatArray = strFormatString.split(strSeparatorArray[intFormatSeparatorIdx]);
    if (strFormatArray.length != 3) {
      return false;
    }

    strDateToCheckArray = strDateToCheck.split(strSeparatorArray[intDateSeparatorIdx]);
    if (strDateToCheckArray.length != 3) {
      return false;
    }

    for (i=0; i<strFormatArray.length; i++) {
      if (strFormatArray[i] == 'mm' || strFormatArray[i] == 'mmm') {
        strMonth = strDateToCheckArray[i];
      }

      if (strFormatArray[i] == 'dd') {
        strDay = strDateToCheckArray[i];
      }

      if (strFormatArray[i] == 'yyyy') {
        strYear = strDateToCheckArray[i];
      }
    }
  } else {
    if (FormatString.length > 7) {
      if (strFormatString.indexOf('mmm') == -1) {
        strMonth = strDateToCheck.substring(strFormatString.indexOf('mm'), 2);
      } else {
        strMonth = strDateToCheck.substring(strFormatString.indexOf('mmm'), 3);
      }

      strDay = strDateToCheck.substring(strFormatString.indexOf('dd'), 2);
      strYear = strDateToCheck.substring(strFormatString.indexOf('yyyy'), 2);
    } else {
      return false;
    }
  }

  if (strYear.length != 4) {
    return false;
  }

  intday = parseInt(strDay, 10);
  if (isNaN(intday)) {
    return false;
  }
  if (intday < 1) {
    return false;
  }

  intMonth = parseInt(strMonth, 10);
  if (isNaN(intMonth)) {
    for (i=0; i<strMonthArray.length; i++) {
      if (strMonth == strMonthArray[i]) {
        intMonth = i+1;
        break;
      }
    }
    if (isNaN(intMonth)) {
      return false;
    }
  }
  if (intMonth > 12 || intMonth < 1) {
    return false;
  }

  intYear = parseInt(strYear, 10);
  if (isNaN(intYear)) {
    return false;
  }
  if (IsLeapYear(intYear) == true) {
    intDaysArray[1] = 29;
  }

  if (intday > intDaysArray[intMonth - 1]) {
    return false;
  }
  
  return true;
}

function IsLeapYear(intYear) {
  if (intYear % 100 == 0) {
    if (intYear % 400 == 0) {
      return true;
    }
  } else {
    if ((intYear % 4) == 0) {
      return true;
    }
  }

  return false;
}
