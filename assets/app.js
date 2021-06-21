
var list=document.getElementById('election-list');
new Sortable(list, {
	animation: 150,
	ghostClass: 'blue-background-class'
});



function submitVotes(){
	if(confirm("Are you sure to submit your votes?")){
		var childs=list.children;
		var res=[];
		for (var i = 0; i<childs.length;i++){
			var cid=childs[i].getAttribute('candidateId');
			res.push(cid);
		}
		console.log(res);
		document.getElementById('votes').value=res.join();
		document.getElementById('elecform').submit();
	}
}