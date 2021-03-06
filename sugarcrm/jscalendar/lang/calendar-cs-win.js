/* 
	calendar-cs-win.js
	language: Czech
	encoding: utf-8
	author: Lubos Jerabek (xnet@seznam.cz)
*/

// ** I18N
Calendar._DN = new Array('Ned��le','Pond��l��','��tery','St?eda','?tvrtek','P��tek','Sobota','Ned��le');
Calendar._SDN = new Array('Ne','Po','��t','St','?t','P��','So','Ne');
Calendar._MN = new Array('Leden','��nor','B?ezen','Duben','Kv��ten','?erven','?ervenec','Srpen','Z��?��','?��jen','Listopad','Prosinec');
Calendar._SMN = new Array('Led','��no','B?e','Dub','Kv��','?rv','?vc','Srp','Z��?','?��j','Lis','Pro');

// tooltips
if(Calendar._TT == undefined) Calendar._TT = {};
Calendar._TT["TOGGLE"] = "Zm��na prvn��ho dne v tydnu";
Calendar._TT["PREV_YEAR"] = "P?edchoz�� rok (p?idr? pro menu)";
Calendar._TT["PREV_MONTH"] = "P?edchoz�� m��s��c (p?idr? pro menu)";
Calendar._TT["GO_TODAY"] = "Dne?n�� datum";
Calendar._TT["NEXT_MONTH"] = "Dal?�� m��s��c (p?idr? pro menu)";
Calendar._TT["NEXT_YEAR"] = "Dal?�� rok (p?idr? pro menu)";
Calendar._TT["SEL_DATE"] = "Vyber datum";
Calendar._TT["DRAG_TO_MOVE"] = "Chy? a t��hni, pro p?esun";
Calendar._TT["PART_TODAY"] = " (dnes)";
Calendar._TT["MON_FIRST"] = "Uka? jako prvn�� Pond��l��";
Calendar._TT["SUN_FIRST"] = "Uka? jako prvn�� Ned��li";
Calendar._TT["CLOSE"] = "Zav?��t";
Calendar._TT["TODAY"] = "Dnes";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "d.m.yy";
Calendar._TT["TT_DATE_FORMAT"] = "%A, %e.%B";

Calendar._TT["WK"] = "wk";
