export const CAMPAIGN_FETCH_DATA_URL = window.baseUrl+ "/client/campaign/getCampaignList";
export const UN_ASSIGN_SINGLE_TAG = baseUrl+"/client/campaign/strategy/tags/singleUnAssign";
export const FILTERATION_DATA = baseUrl+"/client/campaign/campaignTaggingFilter";
export const GET_CAMPAIGN_NAMES_TAGGING = baseUrl+"/client/campaign/getCampaignNamesTagging";


export function getCampaignsData(cb,ecb){
    axios.get(
        CAMPAIGN_FETCH_DATA_URL,
    ).then((response)=>{
        cb(response)
    }).catch((error)=>{
        ecb(error)
    });

}

export function getCampaignTagFilterData(cb,ecb){
    axios.get(
        FILTERATION_DATA,
    ).then((response)=>{
        cb(response)
    }).catch((error)=>{
        ecb(error)
    });

}
export function getCampaignNamesTagging(params,cb, ecb){
    // let url = params.type
    axios.post(
        GET_CAMPAIGN_NAMES_TAGGING,
        {
            fkProfileId: params.value,
            _token: csrf
        }
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

export function unAssignSingleTag(params,cb, ecb){
    // let url = params.type
    axios.post(
        UN_ASSIGN_SINGLE_TAG,
        {
            campaignId: params.campaignId,
            accountId: params.accountId,
            tagId: params.tagId,
            tagType: params.tagType,
            _token: csrf
        }
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