import axios from "axios";
import {profilesMapping} from "../../../helper/helper";
import {DAY_PARTING_DELETE_SCHEDULE_URL} from "../Day-Parting/apiCalls";

export const BUDGET_RULE_PROFILE_URL = window.baseUrl + "/budgetRule/getProfileList";
export const BUDGET_RULE_FETCH_CAMPAIGN_URL = window.baseUrl + "/budgetRule/getCampaignList";
export const GET_RECOMMENDED_URL = window.baseUrl + "/budgetRule/getRecommendationEvent";
export const ADD_BUDGET_RULE_URL = window.baseUrl + "/budgetRule/store";
export const UPDATE_BUDGET_RULE_URL = window.baseUrl + "/budgetRule/update";
export const GET_ALL_BUDGET_RULE_URL = window.baseUrl + "/budgetRule/index";
export const DELETE_BUDGET_RULE_URL = window.baseUrl + "/budgetRule/destroy";


export function getProfiles(params, callback, errorCallback) {
    // Get All Profiles Associated with master brand
    axios.post(BUDGET_RULE_PROFILE_URL + '?_=' + new Date().getTime(), params)
        .then(res => {
            if (callback != null) {
                let profileOptions = profilesMapping(res.data);
                let CampaignsOptions = setCampaignOptions(res.data[0].allCampaigns);
                callback({profileOptions, CampaignsOptions});
            }
        })
        .catch(err => {
            if (errorCallback != null) {
                errorCallback(err);
            }
        })
}

function setCampaignOptions(data) {
    return data.map((obj, idx) => {
        return {
            value: obj.id,
            label: obj.name,
            title:obj.campaignId
        }
    })
}

export function getCampaigns(profileId, adType, callback, errorCallback) {

    axios.post(BUDGET_RULE_FETCH_CAMPAIGN_URL + '?_=' + new Date().getTime(),
        {
            "campaignType": adType,
            "fkProfileId": profileId,
        })
        .then(res => {
            let data = res.data.text;
            if (data.length > 0) {
                data = setCampaignOptions(data)
            }
            if (callback != null) {
                callback(data);
            }

        })
        .catch(err => {
            if (errorCallback != null) {
                errorCallback(err);
            }
        })
}

export function getRecommendedEvents(params, callBack, errorCallback){

    axios.post(GET_RECOMMENDED_URL + '?_=' + new Date().getTime(), params)
        .then((data) => {
            if (callBack != null) {
                callBack(data);
            }
        }).catch((error) => {
        if (errorCallback != null) {
            errorCallback(error);
        }
    })
}

export function addBudgetRule(params, callBack, errorCallback) {
    axios.post(ADD_BUDGET_RULE_URL + '?_=' + new Date().getTime(), params)
        .then((res) => {
            callBack(res.data);
        }).catch((error) => {
        if (errorCallback != null) {
            errorCallback(error);
        }
    })
}

export function updateBudgetRule(params, callBack, errorCallback) {
    axios.post(UPDATE_BUDGET_RULE_URL + '?_=' + new Date().getTime(), params)
        .then((res) => {
            callBack(res.data);
        }).catch((error) => {
        if (errorCallback != null) {
            errorCallback(err);
        }
    })
}

export function getAllBudgetRules(callback, errorCallback) {
    axios.get(GET_ALL_BUDGET_RULE_URL + '?_=' + new Date().getTime())
        .then(res => {
            if (callback != null) {
                callback(res.data);
            }
        })
        .catch(err => {
            if (errorCallback != null) {
                errorCallback(err);
            }
        })
}

export function deleteBudgetRule(params, callback, errorCallback) {
    axios.post(DELETE_BUDGET_RULE_URL + '?_=' + new Date().getTime(), params)
        .then((res) => {
            callback(res.data);
        }).catch((error) => {
        if (errorCallback != null) {
            errorCallback(err);
        }
    })
}