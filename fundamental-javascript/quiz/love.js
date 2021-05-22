let man = "Leo";
let girl = "Christin";

const love = new Promise(function(resolve){
    const trueLove = true;
    if(trueLove)
        resolve('love');
});
love.then(function(result){
    console.log(`I ${result} ${man} !`);
    console.log(`I ${result} ${girl} !`);
});