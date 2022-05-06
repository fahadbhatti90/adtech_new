
export function post(url, params,cb,ecb){
    axios.post(
        url,
        params
    ).then((response)=>{
        if (response.data.status) {
            cb(response.data);
        }else{
            ecb("Error");
        }
    }).catch((error)=>{
        ecb(error)
    });
}


export function get(url, params, cb, ecb) {
    axios.get(
        url,
    {
        params
    }
    ).then((response)=>{
        if(response.data.status){
            cb(response.data);
        }else{
            ecb(response.data.message);    
        }

    }).catch((error)=>{
        if(error.response)
        ecb(error.response.data.message)
        else{
            console.log(error)
            ecb("Some thing went worng in front end app, See Console")
        }
    });
}

export function put(url, params, cb, ecb) {
    axios.put(
        url,
        params
    ).then((response)=>{
        if (response.data.status) {
            cb(response.data);
        }else{
            ecb("Error");
        }
    }).catch((error)=>{
        ecb(error)
    });
}
export function deleteItem(url, cb, ecb) {
    axios.delete(
        url
    ).then((response)=>{
        if (response.data.status) {
            cb();
        }else{
            ecb("Error");
        }
    }).catch((error)=>{
        ecb(error)
    });
}

export function bidMultiplierPut(url, params, cb, ecb) {
    axios.put(
        url,
        params
    ).then((response)=>{
        if (response.data.status) {
            cb(response.data);
        }else{
            ecb("Error");
        }
    }).catch((error)=>{
        ecb(error)
    });
}