class AffiliateNetwork {

    /**
     * 
     * @param string name 
     * @param string tid 
     * @param string postback_subid 
     * @param string postback_transaction 
     * @param string postback_revenue 
     */
    constructor(name, tid, postback_subid, postback_transaction, postback_revenue) {
        this.name = name;
        this.tid = tid;
        this.postback_subid = postback_subid;
        this.postback_transaction = postback_transaction;
        this.postback_revenue = postback_revenue;
    }

    getName() {
        return this.name;
    }

    getTID() {
        return this.tid;
    }

    getPostbackSubID() {
        return this.postback_subid;
    }

    getPostbackTransaction() {
        return this.postback_transaction;
    }

    getPostbackRevenue() {
        return this.postback_revenue;
    }

    getPostbackURL() {
        return ClickerVoltLinkController.replaceVarsFromConvPixel(clickerVoltVars.const.ConvPostbackURLTemplate, {
            'cid': this.getPostbackSubID() || "",
            'type': 'conversion',
            'name': this.getPostbackTransaction() || "",
            'rev': this.getPostbackRevenue() || "",
        });
    }
}

class AffiliateNetworkHelper {

    static fillSelect($select) {
        var networks = AffiliateNetworkHelper.getAllNetworks();
        for (var networkName in networks) {
            $select.append(`<option value="${networkName}">${networkName}</option>`);
        }
    }

    static getAllNetworks() {
        if (!AffiliateNetworkHelper.networks) {
            AffiliateNetworkHelper.networks = {};
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("A4D", "aff_sub", "{aff_sub}", "{transaction_id}", "{payout}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("ActionAds", "aff_sub", "{aff_sub}", "{transaction_id}", "{payout}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("AdCombo", "clickid", "{clickid}", "{trans_id}", "{revenue}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Addiliate", "add1", "%add1%", "%txid%", "%amount%"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Adsimilis", "s2", "#s2#", "#tid#", "#price#"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("AdsUp", "aff_sub", "{aff_sub}", "{transaction_id}", "{payout}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Affiliaxe", "aff_sub", "{aff_sub}", "{transaction_id}", "{payout}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Affise", "sub2", "{sub2}", "{status}-{transactionid}", "{sum}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("AIVIX", "aff_sub", "{aff_sub}", "{transaction_id}", "{cost}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("BeeOption", "aff_sub", "{aff_sub}", "{transaction_id}", "{payout}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Big Bang Ads", "aff_sub", "{aff_sub}", "{transaction_id}", "{payout}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("BillyMob", "sub", "{sub}", null, "{commission}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("BitterStrawberry", "clickid", "{clickid}", "{transaction_id}", "{payout}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("BlackFox", "aff_sub", "{aff_sub}", "{transaction_id}", "{payout}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("C2M", "s2", "#s2#", "#tid#", "#price#"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Cash Network", "s2", "#s2#", "#tid#", "#price#"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("CJ", "sid", null, null, null));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Clickbank", "tid", null, null, null));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Clickbooth", "s2", "#s2#", "#tid#", "#price#"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Clickdealer", "s2", "#s2#", "#tid#", "#price#"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("CPAWay", "subid2", "#subid2#", "#cid#", "#rate#"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("CrackRevenue", "aff_sub", "{aff_sub}", "{transaction_id}", "{payout}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Dr.Cash", "sub1", "{sub1}", "{status}-{uuid}", "{payment}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Everad", "sid1", "{sid1}", "{status}-{id}", "{payout}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Everflow", "sub1", "{sub1}", "{transaction_id}", "{payout_amount}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("ExpertMobi", "subid1", "{{subid1}}", "{{action_id}}", "{{goal_value}}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Generic - CAKE", "s2", "#s2#", "#tid#", "#price#"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Generic - HasOffers", "aff_sub", "{aff_sub}", "{transaction_id}", "{payout}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("GiddyUp", "sub1", "{sub1}", "{transaction_id}", "{payout_amount}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Global Wide Media", "s2", "#s2#", "#tid#", "#price#"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("JVZoo", "tid", null, null, null));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("LeadBit", "sub2", "{sub2}", '{status}-{id}', "{cost}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("LetsCPA", "sub2", "{sub2}", '{status}-{transactionid}', "{sum}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Los Pollos", "cid", "{cid}", null, "{sum}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("LuckyOnline", "subid1", "{get.subid1}", '{status}-{offer_id}', "{amount}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("M4TRIX", "cid", "{cid}", "{orderid}", "{payout}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Magic Hygeia", "aff_sub", "{aff_sub}", "{transaction_id}", "{payout}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("MaxBounty", "s1", "#S1#", "#OFFID#", "#RATE#"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Mobidea", "tag", "{{EXTERNAL_ID}}", null, "{{MONEY}}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Mobidealer", "aff_sub", "{aff_sub}", "{transaction_id}", "{payout}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Mobipium", "clickid", "{tid}", "{uni}", "{pay}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("MobVista", "aff_sub", "{aff_sub}", "{transaction_id}", "{payout}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Shareasale", "afftrack", null, null, null));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("SpicyOffers", "clickid", "[spicy_clickid]", "[spicy_transacid]", "[spicy_payout_dot]"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Tapgerine", "aff_sub", "{aff_sub}", "{transaction_id}", "{payout}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("ToroAdvertising", "aff_sub", "{aff_sub}", "{transaction_id}", "{payout:dot_separated}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Traffic Company", "click_id", "{click_id}", "{unique_id}", "{reward}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Verve Direct (DFO)", "sub1", "{sub1}", "{transaction_id}", "{payout_amount}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("W4", "sid1", "[sid1]", "[clickid]", "[payout]"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("WapEmpire", "aff_sub", "{aff_sub}", "{transaction_id}", "{payout}"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Yep Ads", "s2", "#s2#", "#tid#", "#price#"));
            AffiliateNetworkHelper.registerNetwork(new AffiliateNetwork("Zorka.Mobi", "ref_id", "{ref_id}", "{offer_id}", "{sum}"));
        }
        return AffiliateNetworkHelper.networks;
    }

    static getNetwork(networkName) {
        return AffiliateNetworkHelper.networks[networkName];
    }

    static registerNetwork(network) {
        AffiliateNetworkHelper.networks[network.getName()] = network;
    }
}