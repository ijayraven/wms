function SubClass(){
	SuperClass.call(this);
	this.messagetxt = "";
}
SubClass.prototype = inherit(SuperClass.prototype);

SubClass.prototype.getMsg=function(){
	alert(this.msgText);
}

var SubClassObject = new SubClass();
$("document").ready(function(){
	SubClassObject.msgText = "Hello";
	SubClassObject.SuperClassMethod("Hello");
});
function inherit(proto) {
  function F() {}
  F.prototype = proto
  return new F
}
