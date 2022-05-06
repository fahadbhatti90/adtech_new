export const BID_MULTIPLIER_CAMPAIGN_FETCH_DATA_URL = window.baseUrl+ "/bidMultiplier/campaigns";
export const GET_BID_MULTIPLIER_CHILD_BRANDS = baseUrl+"/bidMultiplier/childBrands";
export const BID_MULTIPLIER_CAMPAIGN_SCHEDULE_FETCH_DATA_URL = baseUrl+"/bidMultiplier/campaigns/schedule";
export const BID_MULTIPLIER_CAMPAIGN_HISTORY_FETCH_DATA_URL = baseUrl+"/bidMultiplier/campaigns/history";
export const BID_MULTIPLIER_CAMPAIGN_HISTORY_DELETE_DATA_URL = baseUrl+"/bidMultiplier/";

export function getBidMultiplierFilterChildBrand(cb,ecb){
    axios.get(
        GET_BID_MULTIPLIER_CHILD_BRANDS,
    ).then((response)=>{
        cb(response)
    }).catch((error)=>{
        ecb(error)
    });
}