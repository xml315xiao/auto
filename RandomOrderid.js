
var orderid = getRandomNumber();
console.log(orderid);
//生成20位的随机数 规则：17位的年月日时分秒毫秒 + 2 + 2位随机数
function getRandomNumber(){
	var chars = ['0','1','2','3','4','5','6','7','8','9'];
	var today = new Date();
	var year = today.getFullYear();
	var month = (today.getMonth()  + 1) < 10  ? '0' + (today.getMonth() + 1) : (today.getMonth() + 1);
	var day      = (today.getDate())  < 10 ? '0' + (today.getDate()) : (today.getDate());
	var hours    = (today.getHours()) < 10 ? '0' + (today.getHours()) : (today.getHours());
	var minutes    = (today.getMinutes()) < 10 ? '0' + (today.getMinutes()) : (today.getMinutes());
	var seconds  = (today.getSeconds()) < 10 ? '0' + (today.getSeconds()) : (today.getSeconds());
	var millisecond = today.getMilliseconds();		
	if(millisecond < 10){						
		millisecond = '00'+ millisecond;
	}else if(10 <= millisecond  &&  millisecond <= 99){			
		millisecond = '0' + millisecond;	
	}	
	var res = "";
	for(var i = 0; i < 2; i++){
		var id = Math.ceil(Math.random()*9);
		res += chars[id];	
	}		
	lastDate = year.toString() +  month.toString() + day.toString() + hours.toString() + minutes.toString() + seconds.toString() + millisecond.toString() + '2' + res.toString();
	return lastDate; 		
}