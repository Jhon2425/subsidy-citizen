import { createSlice } from '@reduxjs/toolkit';

const initialState = {
    stats: {
        totalSeniorCitizens: 0,
        activeBeneficiaries: 0,
        approvedBeneficiaries: 0,
        pendingApplications: 0,
        totalSubsidyDistributed: 0,
    },
    status: {
        pending: 0,
        verification: 0,
        approved: 0,
        rejected: 0,
    },
    thisMonth: {
        subsidy: 0,
        releases: 0,
    },
    monthlyApplications: Array(12).fill(0),
    monthlySubsidies: Array(12).fill(0),
    subsidyBreakdown: [], // [{ name, total_amount }]
};

const analyticsSlice = createSlice({
    name: 'analytics',
    initialState,
    reducers: {
        // Called once on load with the payload Blade injects via
        // window.__DASHBOARD_DATA__. Shallow-merges each top-level key so a
        // partial payload never wipes the rest of the state.
        hydrate(state, action) {
            const payload = action.payload || {};
            return {
                stats: { ...state.stats, ...payload.stats },
                status: { ...state.status, ...payload.status },
                thisMonth: { ...state.thisMonth, ...payload.thisMonth },
                monthlyApplications: payload.monthlyApplications ?? state.monthlyApplications,
                monthlySubsidies: payload.monthlySubsidies ?? state.monthlySubsidies,
                subsidyBreakdown: payload.subsidyBreakdown ?? state.subsidyBreakdown,
            };
        },
    },
});

export const { hydrate } = analyticsSlice.actions;
export default analyticsSlice.reducer;