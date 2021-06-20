
var list=document.getElementById('election-list');
new Sortable(list, {
	animation: 150,
	ghostClass: 'blue-background-class'
});



function submitVotes(){
	var childs=list.children;
	var res="";
	for (var i = 0; i<childs.length;i++){
		var cid=childs[i].getAttribute('candidateId');
		res+=cid+",";
	}
	console.log(res);
	alert("Your selected candidates are "+res);
}