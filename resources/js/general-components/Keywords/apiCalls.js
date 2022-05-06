export function getKeywords(url, cb, ecb){
    axios.get(
        url,
    ).then((response)=>{
        cb(response)
    }).catch((error)=>{
        ecb(error)
    });
}