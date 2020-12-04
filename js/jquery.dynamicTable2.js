//dependency: https://github.com/julien-maurel/js-storage



! function(t) {
    t.fn.extend({
        dynamicTable: function(e) {
            var req={};
			var xhr={}
			
			function i(t, e, i) {
                locStor = Storages.localStorage.get("dynamicTable"), locStor.items = locStor.items.filter(function(i) {
                    return (i.key !== e || i.cls !== t) && i.created > +new Date - 2592e6
                }), i && locStor.items.push({
                    cls: t,
                    key: e,
                    val: i,
                    created: +new Date
                }), Storages.localStorage.set("dynamicTable", locStor)
            }

            function a(a, n, l, c) {
                
				//kilinam sena uzklausa jei buvo paleista (bet nepasibaigusi:4)
				//console.log(xhr);
				if(typeof xhr[l] != "undefined" && xhr[l].readyState != 4) xhr[l].abort();
				
				t("#DTloader_" + l).html('<img src="' + n.img + '" />');
                var r = "";
				//console.log(a, n, l, c);
                void 0 != e.filters ? t.each(e.filters, function() {
                    key = t(this).attr("name"), val = t(this).val(), r += "&" + key + "=" + encodeURIComponent(val), i(e.objId, key, val)
                }) : r += "&q=" + t("#DTsearch_" + l).val(), xhr[l]=t.get(n.url + "&from=" + (t(a).find("tbody tr").length - 1) + "&limit=" + c + r, function(i) {
                    t.each(i, function(i, o) {
                        rowstyle = isNaN(o.rowstyle) ? rowstyle = " " + o.rowstyle + " " : "",  chtml = $("<tr>").attr('style',rowstyle),  t.each(n.cells, function(t, e) {
                            chtml.append(  $('<td>').html(o[e]).attr('data-col',e)   );
                        }), "function" == typeof e.onRow && (chtml = e.onRow(chtml, o)), t(a).find("tbody tr:last").after(chtml)
                    }), "function" == typeof e.onFinish && e.onFinish(t(a)), n.sum && o(a), t("#DTloader_" + l).html("")
                }, "json")
            }

            function o(i) {
                if (e.sum) {
                    t(i).find("tfoot").remove();
                    var a = [];
                    t.each(t(i).find("thead tr th"), function(e, i) {
                        t(i).hasClass("nosum") ? a[e] = !0 : a[e] = !1
                    });
                    var o = t(i).find("tbody tr"),
                        n = [];
                    t.each(o, function(e, i) {
                        var a = t(i).find("td");
                        t.each(a, function(e, i) {
                            skaicius = t(i).html(), skaicius = skaicius.match(/[+-]?\d+(\.\d+)?/), skaicius && (skaicius = parseFloat(skaicius[0])), isNaN(skaicius) && (skaicius = 0), accumulated = parseFloat(n[e]), isNaN(accumulated) && (accumulated = 0), n[e] = accumulated + skaicius
                        })
                    });
                    var l = [];
                    t.each(n, function(t, e) {
                        a[t] ? l[t] = "<th></th>" : l[t] = "<th>" + (e > 0 ? e.toFixed(2) : "-") + "</th>"
                    });
                    var c = "<tfoot><tr>" + l.join("") + "</tr></tfoot>";
                    t(i).find("tbody").after(c)
                }
            }
            var n = {
                    img: "/images/loading.gif",
                    url: "/",
                    sum: !0,
                    more: "rodyti daugiau",
                    all: "rodyti visus",
                    limit: 25,
                    buttons: !0,
                    btnClass:'DTbutton',
                    filterClass:'',
                    filterPlaceholder:'',
                    onRow: "",
                    onFinish: "",
                    bottomBtnClass:'',
                    cells: []
                },
                e = t.extend(n, e),
                l = Math.round(1e5 * Math.random());
            return Storages.localStorage.isSet("dynamicTable") || Storages.localStorage.set("dynamicTable", {
                items: []
            }), this.each(function() {
                var i = this;
                e.objId = t(this).attr("id"), null == e.objId && (e.objId = "tbl_" + l, t(this).attr("id", e.objId));
                var o = t("#" + e.objId + "-filters"),
                    n = null != o.html();
                n || (o = t('<div style="text-align:right" id="' + e.objId + '-filters"><input type="text" size="40" class="'+ e.filterClass +' ' + e.objId + '-filter" placeholder="'+ e.filterPlaceholder +'" name="q" /><input type="button" class="'+ e.btnClass +' reset" value="X" /></div>'), t(i).before(o));
                var c = o.find("." + e.objId + "-filter");
				var filtrai = o;
				var objId = e.objId;
				
                e.filters = c, e.qstr = "", t.each(c, function() {
                    var o = t(this);
                    locStor = Storages.localStorage.get("dynamicTable"), locStor.items = locStor.items.filter(function(t) {
                        return t.key === o.attr("name") && t.cls === e.objId
                    }), "undefined" != typeof locStor.items[0] ? cval = locStor.items[0].val : cval = "", "" != cval && o.val(cval), o.on("keyup change", function(e) {
                        ("text" != o.attr("type") || "change" != e.type && "keyup" != e.type) && "change" != e.type || s(function() {
                            //vykdom paieska trigerinant search filtra. reikia nevykdyt, jei paksutine paiesko tokia pat buvo
							
							//if (typeof window.dynamicTable == "undefined") window.dynamicTable = {};
							var paskutinePaieska= (typeof req[objId] == "undefined") ? null : req[objId] ;
							var dabartinePaieska = filtrai.find("." + objId + "-filter").serialize();
							//console.log(dabartinePaieska);
							if (paskutinePaieska != dabartinePaieska) {
								req[objId] = dabartinePaieska;
								t(i).find("tbody tr:gt(0)").remove(), a(i, r, l, r.limit);  
							}
							
							//t(i).find("tbody tr:gt(0)").remove(), a(i, r, l, r.limit); 
							
                        }, 700)
                    })
                }), o.find(".reset").click(function() {
					if (typeof req[objId] != "undefined") delete req[objId];
					t.each(c, function() {
						t(this).val("").trigger("change");
						/*
						s(function() {
                            t(i).find("tbody tr:gt(0)").remove(), a(i, r, l, r.limit)
                        }, 700)*/
						
                    })
                });
                var r = e;
                r.buttons && (t(i).after('<div class="'+ r.bottomBtnClass +'"><a id="DTmore_' + l + '" class="'+ r.btnClass +' more" href="javascript:void(0)">' + r.more + '</a> <a id="DTall_' + l + '" class="'+ r.btnClass +' all" href="javascript:void(0)">' + r.all + '</a> <span id="DTloader_' + l + '"></span></div>'), t("#DTmore_" + l).click(function() {
                    a(i, r, l, r.limit)
                }), t("#DTall_" + l).click(function() {
                    a(i, r, l, 99999)
                })), a(i, r, l, r.limit);
                var s = function() {
                    var t = 0;
                    return function(e, i) {
                        clearTimeout(t), t = setTimeout(e, i)
                    }
                }()
            })
        }
    })
}(jQuery);
