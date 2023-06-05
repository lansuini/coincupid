$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    jQuery.fn.extend({
        initSearch: function (d, e, c) {
            if (c == undefined) {
                c = {}
            }
            var b = c.tabId || "tabMain";
            var f = c.searchContainerId || "divSearch";
            var sort = c['sortName'] || '';
            var sortorder = c['sortOrder'] || '';
            var a = {
                url: d,
                queryParams: g,
                columns: e,
                pagination: true,
                sidePagination: "server",
                pageList: [10, 20, 30, 50, 100],
                pageSize: 20,
                cache: false,
                striped: true,
                sortable: true,
                clickToSelect: true,
                // onResetView: function () {
                //     $("#" + b).parent().parent().parent().find(">div.fixed-table-summary").remove()
                // },
                onLoadSuccess: function (h) {
                    if (c.success_callback && c.success_callback instanceof Function) {
                        c.success_callback(h)
                    }
                },
                onSort: function (name, order) {
                    sort = name
                    sortorder = order
                    // $("#" + b).bootstrapTable('refreshOptions', {
                    //     sortName:name,
                    //     sortOrder:order
                    // })
                }
            };
            $("#" + b).bootstrapTable($.extend({}, a, c));
            $(this).click(function () {
                $("#" + b).bootstrapTable('refreshOptions', { pageNumber: 1 });
                $("#" + b).bootstrapTable("selectPage", 1)
            });
            function g(i) {
                var h = common.getFields(f);
                h.limit = i.limit;
                h.offset = i.offset;
                h.sort = sort;
                h.order = sortorder;
                return h
            }
        },
        initExport: function (d, e, c) {
            if (c == undefined) {
                c = {}
            }
            var b = c.tabId || "tabMain";
            var f = c.searchContainerId || "divSearch";
            url = d + '?' + myFunction.parseParams(g())
            window.location.href = url
            function g() {
                var h = common.getFields(f);
                h.export = 1;
                return h
            }
        },
        initSelect: function (h, d, a, e, i, g) {
            var b = $(this);
            if (e) {
                b.append("<option value=''>" + e + "</option>")
            }
            for (var f = 0, c = h.length; f < c; f++) {
                b.append("<option value='" + h[f][d] + "'>" + h[f][a] + "</option>")
                // console.log("<option value='" + h[f][d] + "'>" + h[f][a] + "</option>");
            }
            b.find("option" + (i ? "[value='" + i + "']" : ":first")).attr("selected", true);
            if (g && g instanceof Function) {
                g()
            }
        },
        initExportTable: function (a, b) {
            $(this).bootstrapTable({
                columns: a,
                sidePagination: "server",
                pagination: true,
                cache: false,
                sortable: false,
                onLoadSuccess: function (c) {
                    b()
                }
            })
        },
        initSelectToggle: function (a, b) {
            $(this).change(function () {
                var c = $("#" + a);
                c.val("").attr("disabled", "disabled");
                if ($(this).val() == b) {
                    c.removeAttr("disabled")
                }
            });
            $("#" + a).attr("disabled", "disabled")
        }
    });
    $("#lang").change(function(){
        $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});
        $.post(
            "admin/setLang/" + $("#lang").val(),
            function(d){
                if(d){
                    console.log(d);
                    location.reload(); //重新刷新页面
                }
            }
        );
    });
});
var myAlert = {
    success: function (b, a, c) {
        this.show("success", b, a, c)
    },
    warning: function (b, a, c) {
        this.show("warning", b, a, c)
    },
    error: function (b, a, c) {
        this.show("error", b, a, c)
    },
    show: function (a, c, b, d) {
        Swal.fire({
            title: c,
            icon: a ? a : "success",
            confirmButtonText: b ? b : "Sure",
            allowOutsideClick: false
        }).then(function () {
            if (d && d instanceof Function) {
                d()
            }
        })
    }
};

var formError = {
    n: 0,
    s: 0,
    clear: function (a) {
        formError.n = 0
        $('#' + a).find(".is-invalid").removeClass('is-invalid')
        $('#' + a).find('span.invalid-feedback').remove()
    },
    show: function (a, d) {
        if ('validator' in d) {
            for (var i in d.validator) {
                formError.n++
                $('#' + a).find("[data-field='" + i + "']")
                    .addClass('is-invalid')
                    .after('<span class="error invalid-feedback">' + d.validator[i][0] + '</span>')
                if (formError.n == 1) {
                    $('#' + a).find("[data-field='" + i + "']").focus()
                }
            }
            return true
        }
        return false
    }
}

var cform = {
    _error: function (jqXHR, textStatus, errorThrown) {
        formError.s = 0
        $('#ajaxloading').remove()
        myAlert.error('[' + jqXHR.status + ']' + textStatus + ':' + errorThrown)
        console.log(jqXHR)
    },
    getValue: function (data, key) {
        for (var i = 0; i < data.length; i++) {

            if (data[i]['key'] == key) {
                try {
                    var cls = 'txt-class' in data[i] ? data[i]['txt-class'] : ''
                } catch (e) {
                    var cls = ''
                }
                return '<span class="' + cls + '">' + data[i]['value'] + '</span>';
            }
        }
        return key
    },
    get: function (a, b, c) {
        // common.getAjax(b, function(d) {
        //     for (var i in d.data) {
        //         $('#' + a + ' form').find("[data-field='" + i + "']").val(d[i])
        //     }
        //     $('#' + a).modal()
        // })
        formError.clear(a)
        $('#' + a).find('form').trigger('reset');
        $.ajax({
            url: b,
            dataType: 'json',
            type: "get",
            success: function (d) {
                if (d) {
                    if (d.success == 1) {
                        for (var i in d.data) {
                            $('#' + a + ' form').find("[data-field='" + i + "']").val(d.data[i])
                        }
                        $('#' + a).modal()
                        if (c !== undefined) {
                            c(d)
                        }
                    } else if (d.success == 0) {
                        formError.show(a, d) || myAlert.error(d.result)
                    }
                } else {
                    myAlert.error(d.result.length > 0 ? d.result : "network error")
                }
            },
            error: cform._error
        })
    },
    post: function (a, b, c) {
        if (formError.s == 1) return
        formError.s = 1
        formError.clear(a)
        $('#' + a).find('.modal-footer').append('<div id="ajaxloading" class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>')
        
        // $('#' + a).find('.modal-footer').append('<div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>')
        // $('#' + a).find('.modal-footer').find('input').hide()
        $.ajax({
            url: b,
            dataType: 'json',
            type: "post",
            data: common.getFields(a),
            success: function (d) {
                formError.s = 0
                // $('#' + a).find('.modal-footer').find('input').show()
                // $('#' + a).find('.modal-footer').find('.spinner-border').remove()
                $('#ajaxloading').remove()
                if (d) {
                    if (d.success == 1) {
                        if (c !== undefined) {
                            c(d)
                        } else {
                            myAlert.success(d.result)
                            location.href = location.href
                        }
                    } else if (d.success == 0) {
                        formError.show(a, d) || myAlert.error(d.result)
                    }
                } else {
                    myAlert.error(d.result.length > 0 ? d.result : "network error")
                }
            },
            error: cform._error
        })
    },
    patch: function (a, b, c, d) {
        if (formError.s == 1) return
        formError.s = 1
        // console.log(d || common.getFields(a))
        formError.clear(a)
        $('#' + a).find('.modal-footer').append('<div id="ajaxloading" class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>')
        // $('#' + a).find('.modal-footer').find('input').hide()
        $.ajax({
            url: b,
            dataType: 'json',
            type: "patch",
            data: common.getFields(a),
            success: function (d) {
                formError.s = 0
                // $('#' + a).find('.modal-footer').find('input').show()
                $('#ajaxloading').remove()
                if (d) {
                    if (d.success == 1) {
                        if (c !== undefined) {
                            c(d)
                        } else {
                            myAlert.success(d.result)
                            location.href = location.href
                        }
                    } else if (d.success == 0) {
                        formError.show(a, d) || myAlert.error(d.result)
                    }
                } else {
                    myAlert.error(d.result.length > 0 ? d.result : "network error")
                }
            },
            error: cform._error
        })
    },

    patch2: function (a, b, c, d) {
        if (formError.s == 1) return
        formError.s = 1
        formError.clear(a)
        $('#' + a).find('.modal-footer').append('<div id="ajaxloading" class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div>')
        // $('#' + a).find('.modal-footer').find('input').hide()
        $.ajax({
            url: b,
            type: "patch",
            data: d || common.getFields(a),
            contentType: "application/json; charset=utf-8",
            // dataType: 'json',
            success: function (d) {
                formError.s = 0
                // $('#' + a).find('.modal-footer').find('input').show()
                // $('#' + a).find('.modal-footer').find('.spinner-border').remove()
                $('#ajaxloading').remove()
                c(d)
            },
            error: cform._error
        })
    },

    del: function (b, c) {
        $.ajax({
            url: b,
            dataType: 'json',
            type: "delete",
            success: function (d) {
                if (d) {
                    if (d.success == 1) {
                        if (c !== undefined) {
                            c(d)
                        } else {
                            myAlert.success(d.result)
                            location.href = location.href
                        }
                    } else if (d.success == 0) {
                        myAlert.error(d.result)
                    }
                } else {
                    myAlert.error(d.result.length > 0 ? d.result : "network error")
                }
            },
            error: cform._error
        })
    }
}
var myConfirm = {
    show: function (a) {
        Swal.fire({
            title: a.title,
            text: a.text,
            icon: "warning",
            allowOutsideClick: false,
            showCancelButton: true,
            confirmButtonText: a.confirmButtonText ? a.confirmButtonText : "Sure",
            cancelButtonText: a.cancelButtonText ? a.cancelButtonText : "Cancel"
        }).then(function (b) {
            if (a.sure_callback && a.sure_callback instanceof Function && b.value) {
                a.sure_callback()
            } else {
                if (a.cancel_callback && a.cancel_callback instanceof Function && b.dismiss == "cancel") {
                    a.cancel_callback()
                }
            }
        })
    }
};
var common = {
    ya: function(a) {
        return (parseInt(a) / 100).toFixed(2)
    },

    getAjax: function (b, c, a) {
        $.ajax({
            url: b,
            cache: false,
            type: "get",
            dataType: "json",
            contentType: "application/json",
            success: function (d) {
                if (d) {
                    if (d.success == -1) {
                        location.href = "/logout";
                        return
                    }
                    if (c && c instanceof Function) {
                        c(d)
                    }
                } else {
                    myAlert.error(d.result.length > 0 ? d.result : "Action Exception!")
                }
            },
            error: function (d) {
                if (a && a instanceof Function) {
                    a(d)
                }
            }
        })
    },

    submit: function (b, a, c, f) {
        this.getAjax(this.perfectUrl(b, a), function (d) {
            if (d.success) {
                myAlert.success("Successed!", undefined, function () {
                    if (c && c instanceof Function) {
                        c()
                    }
                })
            } else {
                if (f) {
                    f()
                }
                myAlert.error(d.result.length > 0 ? d.result : "Action Exception!")
            }
        }, function (d) {
            if (f) {
                f()
            }
            myAlert.error("failed!")
        })
    },
    uploadFile: function (b, c, d, a) {
        $.ajax({
            url: b,
            type: "post",
            data: c,
            processData: false,
            contentType: false,
            success: function (e) {
                if (e) {
                    try {
                        var success = e.success;
                    }
                    catch (err) {
                        e = JSON.parse(e);
                        var success = e.success;
                    }
                    // e = JSON.parse(e);
                    if (success == -1) {
                        location.href = "/logout";
                        return
                    }
                    if (d && d instanceof Function) {
                        d(e)
                    }

                    /* location.href = location.href; */
                } else {
                    myAlert.error("Action Exception!")
                }
            },
            error: function (f) {
                if (a && a instanceof Function) {
                    a(f)
                }
            }
        })
    },
    getQuery: function (d) {
        var c = location.search;
        if (c.length > 0) {
            c = decodeURI(c);
            var a = c.substring(1).split("&");
            d += "=";
            for (var b = 0; b < a.length; b++) {
                if (a[b].indexOf(d) == 0) {
                    return a[b].substring(d.length)
                }
            }
        }
        return undefined
    },
    perfectUrl: function (c, b) {
        var d = this.getFields(b);
        var a = new Array();
        for (var e in d) {
            a.push(e + "=" + encodeURIComponent(d[e]))
        }
        if (a.length > 0) {
            c += (c.indexOf("?") == -1 ? "?" : "&") + a.join("&")
        }
        return c
    },
    getFields: function (c) {
        var b = {};
        if (c) {
            var a = $("#" + c + " *[data-field]");
            for (var d = 0; d < a.length; d++) {
                var e = $(a[d]);
                if (e.attr("type") == "checkbox") {
                    if (e.is(":checked")) {
                        b[e.attr("data-field")] = ""
                    }
                } else {
                    if (e.val() != "") {
                        if (e.attr("data-field") in b) {
                            b[e.attr("data-field")] = b[e.attr("data-field")] + ',' + e.val().trim()
                        } else {
                            try{
                                b[e.attr("data-field")] = e.val().trim()
                            }catch(e) {
                                console.log(e)
                            }
                          
                        }
                    }
                }
            }
        }
        return b
    },
    toDateStr: function (f, d) {
        try {
            if (d == null) {
                return ""
            }
            var b = new Date(d.time);
            var a = {
                yyyy: b.getFullYear(),
                MM: b.getMonth() + 1,
                dd: b.getDate(),
                HH: b.getHours(),
                mm: b.getMinutes(),
                ss: b.getSeconds()
            };
            for (var g in a) {
                if (new RegExp("(" + g + ")").test(f)) {
                    f = f.replace(RegExp.$1, (a[g] < 10 ? "0" : "") + a[g])
                }
            }
            return f
        } catch (c) {
            return ""
        }
    },
    fixAmount: function (a) {
        if (a == undefined) {
            return ""
        }
        a = parseFloat(a);
        a = a.toFixed(6).toString();
        return a.substring(0, a.indexOf(".") + 3)
    },
    isInt: function (c, a) {
        var b = a ? /(^0$)|(^[1-9]{1}\d*$)/ : /^[1-9]{1}\d*$/;
        return c != undefined && b.test(c)
    },
    isDecimal: function (c, a) {
        var b = a == undefined ? /^\d{1,}(.\d{0,2})?$/ : /^\d{1,}(.\d{0,6})?$/;
        if (c != undefined) {
            if (c.toString().indexOf(".") == -1) {
                return this.isInt(c, true) && b.test(c)
            } else {
                return b.test(c)
            }
        }
        return false
    },
    logout: function () {
        myConfirm.show({
            title: "您确定要退出系统？",
            sure_callback: function () {
                location.href = "/logout";
            }
        })
    },
    initSection: function (c, floag) {
        var b = "txtBeginTime";
        var e = "txtEndTime";
        var d = {
            format: "yyyy-mm-dd hh:ii:ss",
            language: "zh-CN",
            autoclose: true,
            startView: 2,
            weekStart: 1,
            todayBtn: "linked",
            todayBtn: true,
            minuteStep: 1,
        };
        if (c) {
            b = "txtBeginDate";
            e = "txtEndDate";
            d.format = "yyyy-mm-dd";
            d.minView = 2
        }
        a(b, e, true);
        a(e, b, false);
        function a(h, g, f) {
            if (floag) {
                var day2 = new Date();
                if (f) {
                    if (c) {
                        $("#" + h).val(day2.format("yyyy-MM-dd"));
                        $("#" + g).val(day2.format("yyyy-MM-dd"));
                    } else {
                        $("#" + h).val(day2.format("yyyy-MM-dd 00:00:00"));
                        $("#" + g).val(day2.format("yyyy-MM-dd hh:mm:ss"));
                    }
                } else {
                    if (c) {
                        $("#" + h).val(day2.format("yyyy-MM-dd"));
                        $("#" + g).val(day2.format("yyyy-MM-dd"));
                    } else {
                        $("#" + h).val(day2.format("yyyy-MM-dd hh:mm:ss"));
                        $("#" + g).val(day2.format("yyyy-MM-dd 00:00:00"));
                    }
                }
            }

            $("#" + h).datetimepicker("remove").datetimepicker(d).on("changeDate", function (i) {
                // console.log(i.date);
                $("#" + g).datetimepicker(f ? "setStartDate" : "setEndDate", i.date)
            })
        }
    }
};
var myFunction = {
    //json 转URL 传参格式
    parseParams: function (data) {
        try {
            var tempArr = [];
            for (var i in data) {
                var key = encodeURIComponent(i);
                var value = encodeURIComponent(data[i]);
                tempArr.push(key + '=' + value);
            }
            var urlParamsStr = tempArr.join('&');
            return urlParamsStr;
        } catch (err) {
            return '';
        }
    },
    //URL 传参格式  转json
    getParams: function (url) {
        try {
            var index = url.indexOf('?');
            url = url.match(/\?([^#]+)/)[1];
            var obj = {}, arr = url.split('&');
            for (var i = 0; i < arr.length; i++) {
                var subArr = arr[i].split('=');
                var key = decodeURIComponent(subArr[0]);
                var value = decodeURIComponent(subArr[1]);
                obj[key] = value;
            }
            return obj;

        } catch (err) {
            return null;
        }
    }
};
/**
 *对Date的扩展，将 Date 转化为指定格式的String
 *月(M)、日(d)、小时(h)、分(m)、秒(s)、季度(q) 可以用 1-2 个占位符，
 *年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字)
 *例子：
 *(new Date()).Format("yyyy-MM-dd hh:mm:ss.S") ==> 2006-07-02 08:09:04.423
 *(new Date()).Format("yyyy-M-d h:m:s.S")      ==> 2006-7-2 8:9:4.18
 */
Date.prototype.format = function (fmt) {
    var o = {
        "M+": this.getMonth() + 1, //月份
        "d+": this.getDate(), //日
        "h+": this.getHours(), //小时
        "m+": this.getMinutes(), //分
        "s+": this.getSeconds(), //秒
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度
        "S": this.getMilliseconds() //毫秒
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
        if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
}
