import {profileMappingAlerts} from "../../../helper/helper";
import axios from "axios";
export const VIEW_ALERTS_URL = window.baseUrl + "/viewAlerts";
export const GET_CHILD_BRAND_OPTIONS = window.baseUrl + "/getAlertChildBrand";
export const ADD_ALERTS_URL = window.baseUrl + "/addAlert";
export const UPDATE_ALERTS_URL = window.baseUrl + "/updateAlert";
export const DELETE_ALERTS_URL = window.baseUrl + "/deleteAlert";


export function getAlertsDataApi(callback, errorCallback){
    axios.get(VIEW_ALERTS_URL)
        .then(res => {
            if(callback != null){
                let data = res.data;
                for(let i=0;i<data.length;++i){
                    data[i]["serial"] = i+1;
                }
                callback(data);
            }
        })
        .catch(err => {
            if(errorCallback != null){
                errorCallback(err);
            }
        })
}

export function getChildBrandData(callback, errorCallback){
    axios.get(GET_CHILD_BRAND_OPTIONS)
        .then(res => {
            if(callback != null){
                let data = profileMappingAlerts(res.data);
                callback(data);
            }
        })
        .catch(err => {
            if(errorCallback != null){
                errorCallback(err);
            }
        })
}

export function storeAlertForm(params, callback, errorCallback) {
    axios.post(ADD_ALERTS_URL + '?_=' + new Date().getTime(), params)
        .then((res) => {
            callback(res.data);
        }).catch((error) => {
        if (errorCallback != null) {
            errorCallback(error);
        }
    })
}

export function updateAlertForm(params, callback, errorCallback) {
    axios.post(UPDATE_ALERTS_URL + '?_=' + new Date().getTime(), params)
        .then((res) => {
            callback(res.data);
        }).catch((error) => {
        if (errorCallback != null) {
            errorCallback(error);
        }
    })
}

export function deleteAlertForm(params, callback, errorCallback) {
    axios.post(DELETE_ALERTS_URL + '?_=' + new Date().getTime(), params)
        .then((res) => {
            callback(res.data);
        }).catch((error) => {
        if (errorCallback != null) {
            errorCallback(error);
        }
    })
}