function foo() {
    var bar = function () {
        return 12;
    };

    return bar();

    var bar = function(){
        return 10;
    };
}

console.log(foo());