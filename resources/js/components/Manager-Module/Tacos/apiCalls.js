export const TACOS_CAMPAIGN_FETCH_DATA_URL = window.baseUrl+ "/tacos/campaigns";
export const GET_TACOS_CHILD_BRANDS = baseUrl+"/tacos/childBrands";
export const TACOS_CAMPAIGN_SCHEDULE_FETCH_DATA_URL = baseUrl+"/tacos/campaigns/schedule";
export const TACOS_CAMPAIGN_HISTORY_FETCH_DATA_URL = baseUrl+"/tacos/campaigns/history";
export const TACOS_CAMPAIGN_HISTORY_DELETE_DATA_URL = baseUrl+"/tacos/";


export function getTacosFilterChildBrand(cb,ecb){
    axios.get(
        GET_TACOS_CHILD_BRANDS,
    ).then((response)=>{
        cb(response)
    }).catch((error)=>{
        ecb(error)
    });

}

