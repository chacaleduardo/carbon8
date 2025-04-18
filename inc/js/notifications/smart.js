/*! SmartAdmin - v1.4.1 - 2014-06-22 */

function SmartUnLoading() {
	$(".divMessageBox").fadeOut(300, function() {
		$(this).remove()
	}), $(".LoadingBoxContainer").fadeOut(300, function() {
		$(this).remove()
	})
}

function getInternetExplorerVersion() {
	var a = -1;
	if ("Microsoft Internet Explorer" == navigator.appName) {
		var b = navigator.userAgent,
			c = new RegExp("MSIE ([0-9]{1,}[.0-9]{0,})");
		null != c.exec(b) && (a = parseFloat(RegExp.$1))
	}
	return a
}

function checkVersion() {
	var a = "You're not using Windows Internet Explorer.",
		b = getInternetExplorerVersion();
	b > -1 && (a = b >= 8 ? "You're using a recent copy of Windows Internet Explorer." : "You should upgrade your copy of Windows Internet Explorer."), alert(a)
}

function isIE8orlower() {
	var a = "0",
		b = getInternetExplorerVersion();
	return b > -1 && (a = b >= 9 ? 0 : 1), a
}
$.sound_path = "sound/", $.sound_on = !0, jQuery(document).ready(function() {
	$("body").append("<div id='divSmallBoxes'></div>"), $("body").append("<div id='divMiniIcons'></div><div id='divbigBoxes'></div>")
});
var ExistMsg = 0,
	SmartMSGboxCount = 0,
	PrevTop = 0;
$.SmartMessageBox = function(a, b) {
	var c, d;
	a = $.extend({
		title: "",
		content: "",
		NormalButton: void 0,
		ActiveButton: void 0,
		buttons: void 0,
		input: void 0,
		inputValue: void 0,
		placeholder: "",
		options: void 0
	}, a);
	var e = 0;
	if (e = 1, 0 == isIE8orlower()) {
		var f = document.createElement("audio");
		f.setAttribute("src", $.sound_path + "messagebox.mp3"), /* $.get(), */ f.addEventListener("load", function() {
			f.play()
		}, !0), $.sound_on && (f.pause(), f.play())
	}
	SmartMSGboxCount += 1, 0 == ExistMsg && (ExistMsg = 1, c = "<div class='divMessageBox animated fadeIn fast' id='MsgBoxBack'></div>", $("body").append(c), 1 == isIE8orlower() && $("#MsgBoxBack").addClass("MessageIE"));
	var g = "",
		h = 0;
	if (void 0 != a.input) switch (h = 1, a.input = a.input.toLowerCase(), a.input) {
		case "text":
			a.inputValue = "string" === $.type(a.inputValue) ? a.inputValue.replace(/'/g, "&#x27;") : a.inputValue, g = "<input class='form-control' type='" + a.input + "' id='txt" + SmartMSGboxCount + "' placeholder='" + a.placeholder + "' value='" + a.inputValue + "'/><br/><br/>";
			break;
		case "password":
			g = "<input class='form-control' type='" + a.input + "' id='txt" + SmartMSGboxCount + "' placeholder='" + a.placeholder + "'/><br/><br/>";
			break;
		case "select":
			if (void 0 == a.options) alert("For this type of input, the options parameter is required.");
			else {
				g = "<select class='form-control' id='txt" + SmartMSGboxCount + "'>";
				for (var i = 0; i <= a.options.length - 1; i++) "[" == a.options[i] ? j = "" : "]" == a.options[i] ? (k += 1, j = "<option>" + j + "</option>", g += j) : j += a.options[i];
				g += "</select>"
			}
			break;
		default:
			alert("That type of input is not handled yet")
	}
	d = "<div class='MessageBoxContainer animated fadeIn fast' id='Msg" + SmartMSGboxCount + "'>", d += "<div class='MessageBoxMiddle'>", d += "<span class='MsgTitle'>" + a.title + "</span class='MsgTitle'>", d += "<p class='pText'>" + a.content + "</p>", d += g, d += "<div class='MessageBoxButtonSection'>", void 0 == a.buttons && (a.buttons = "[Accept]"), a.buttons = $.trim(a.buttons), a.buttons = a.buttons.split("");
	var j = "",
		k = 0;
	void 0 == a.NormalButton && (a.NormalButton = "#232323"), void 0 == a.ActiveButton && (a.ActiveButton = "#ed145b");
	for (var i = 0; i <= a.buttons.length - 1; i++) "[" == a.buttons[i] ? j = "" : "]" == a.buttons[i] ? (k += 1, j = "<button id='bot" + k + "-Msg" + SmartMSGboxCount + "' class='btn btn-default btn-sm botTempo'> " + j + "</button>", d += j) : j += a.buttons[i];
	d += "</div>", d += "</div>", d += "</div>", SmartMSGboxCount > 1 && ($(".MessageBoxContainer").hide(), $(".MessageBoxContainer").css("z-index", 99999)), $(".divMessageBox").append(d), 1 == h && $("#txt" + SmartMSGboxCount).focus(), $(".botTempo").hover(function() {
		$(this).attr("id")
	}, function() {
		$(this).attr("id")
	}), $(".botTempo").click(function() {
		var a = $(this).attr("id"),
			c = a.substr(a.indexOf("-") + 1),
			d = $.trim($(this).text());
		if (1 == h) {
			if ("function" == typeof b) {
				var e = c.replace("Msg", ""),
					f = $("#txt" + e).val();
				b && b(d, f)
			}
		} else "function" == typeof b && b && b(d);
		$("#" + c).addClass("animated fadeOut fast"), SmartMSGboxCount -= 1, 0 == SmartMSGboxCount && $("#MsgBoxBack").removeClass("fadeIn").addClass("fadeOut").delay(300).queue(function() {
			ExistMsg = 0, $(this).remove()
		})
	})
};
var BigBoxes = 0;
$.bigBox = function(a, b) {
	var c;
	if (a = $.extend({
		title: "",
		content: "",
		icon: void 0,
		number: void 0,
		color: void 0,
		sound: $.sound_on,
		sound_file: "bigbox",
		timeout: void 0,
		colortime: 1500,
		colors: void 0
	}, a), 1 == a.sound && 0 == isIE8orlower()) {
		var d = document.createElement("audio");
		navigator.userAgent.match("Firefox/") ? d.setAttribute("src", $.sound_path + a.sound_file + ".ogg") : d.setAttribute("src", $.sound_path + a.sound_file + ".mp3"), /* $.get(), */ d.addEventListener("load", function() {
			d.play()
		}, !0), $.sound_on && (d.pause(), d.play())
	}
	BigBoxes += 1, c = "<div id='bigBox" + BigBoxes + "' class='bigBox animated fadeIn fast'><div id='bigBoxColor" + BigBoxes + "'><i class='botClose fa fa-times' id='botClose" + BigBoxes + "'></i>", c += "<span>" + a.title + "</span>", c += "<p>" + a.content + "</p>", c += "<div class='bigboxicon'>", void 0 == a.icon && (a.icon = "fa fa-cloud"), c += "<i class='" + a.icon + "'></i>", c += "</div>", c += "<div class='bigboxnumber'>", void 0 != a.number && (c += a.number), c += "</div></div>", c += "</div>", $("#divbigBoxes").append(c), void 0 == a.color && (a.color = "#004d60"), $("#bigBox" + BigBoxes).css("background-color", a.color), $("#divMiniIcons").append("<div id='miniIcon" + BigBoxes + "' class='cajita animated fadeIn' style='background-color: " + a.color + ";'><i class='" + a.icon + "'/></i></div>"), $("#miniIcon" + BigBoxes).bind("click", function() {
		var a = $(this).attr("id"),
			b = a.replace("miniIcon", "bigBox"),
			c = a.replace("miniIcon", "bigBoxColor");
		$(".cajita").each(function() {
			var a = $(this).attr("id"),
				b = a.replace("miniIcon", "bigBox");
			$("#" + b).css("z-index", 9998)
		}), $("#" + b).css("z-index", 9999), $("#" + c).removeClass("animated fadeIn").delay(1).queue(function() {
			$(this).show(), $(this).addClass("animated fadeIn"), $(this).clearQueue()
		})
	});
	var e, f = $("#botClose" + BigBoxes),
		g = $("#bigBox" + BigBoxes),
		h = $("#miniIcon" + BigBoxes);
	if (void 0 != a.colors && a.colors.length > 0 && (f.attr("colorcount", "0"), e = setInterval(function() {
		var b = f.attr("colorcount");
		f.animate({
			backgroundColor: a.colors[b].color
		}), g.animate({
			backgroundColor: a.colors[b].color
		}), h.animate({
			backgroundColor: a.colors[b].color
		}), b < a.colors.length - 1 ? f.attr("colorcount", 1 * b + 1) : f.attr("colorcount", 0)
	}, a.colortime)), f.bind("click", function() {
		clearInterval(e), "function" == typeof b && b && b();
		var a = $(this).attr("id"),
			c = a.replace("botClose", "bigBox"),
			d = a.replace("botClose", "miniIcon");
		$("#" + c).removeClass("fadeIn fast"), $("#" + c).addClass("fadeOut fast").delay(300).queue(function() {
			$(this).clearQueue(), $(this).remove()
		}), $("#" + d).removeClass("fadeIn fast"), $("#" + d).addClass("fadeOut fast").delay(300).queue(function() {
			$(this).clearQueue(), $(this).remove()
		})
	}), void 0 != a.timeout) {
		var i = BigBoxes;
		setTimeout(function() {
			clearInterval(e), $("#bigBox" + i).removeClass("fadeIn fast"), $("#bigBox" + i).addClass("fadeOut fast").delay(300).queue(function() {
				$(this).clearQueue(), $(this).remove()
			}), $("#miniIcon" + i).removeClass("fadeIn fast"), $("#miniIcon" + i).addClass("fadeOut fast").delay(300).queue(function() {
				$(this).clearQueue(), $(this).remove()
			})
		}, a.timeout)
	}
};
var SmallBoxes = 0,
	SmallCount = 0,
	SmallBoxesAnchos = 0;
$.smallBox = function(a, b) {
	var c;
	if (a = $.extend({
		title: "",
		content: "",
		icon: void 0,
		iconSmall: void 0,
		sound: $.sound_on,
		sound_file: "smallbox",
		color: void 0,
		timeout: void 0,
		colortime: 1500,
		colors: void 0
	}, a), 1 == a.sound && 0 == isIE8orlower()) {
		var d = document.createElement("audio");
		
		navigator.userAgent.match("Firefox/") ? d.setAttribute("src", $.sound_path + a.sound_file + ".ogg") : d.setAttribute("src", $.sound_path + a.sound_file + ".mp3"), 
		/* $.get(), */ d.addEventListener("load", function() {
		    d.play();
		}, !0), $.sound_on && (d.pause(), d.play());
	}
	SmallBoxes += 1, c = "";
	var e = "",
		f = "smallbox" + SmallBoxes;
	if (e = void 0 == a.iconSmall ? "<div class='miniIcono'></div>" : "<div class='miniIcono'><i class='miniPic " + a.iconSmall + "'></i></div>", c = void 0 == a.icon ? "<div id='smallbox" + SmallBoxes + "' class='SmallBox animated fadeInRight fast'><div class='textoFull'><span>" + a.title + "</span><p>" + a.content + "</p></div>" + e + "</div>" : "<div id='smallbox" + SmallBoxes + "' class='SmallBox animated fadeInRight fast'><div class='foto'><i class='" + a.icon + "'></i></div><div class='textoFoto'><span>" + a.title + "</span><p>" + a.content + "</p></div>" + e + "</div>", 1 == SmallBoxes) $("#divSmallBoxes").append(c), SmallBoxesAnchos = $("#smallbox" + SmallBoxes).height() + 40;
	else {
		var g = $(".SmallBox").size();
		0 == g ? ($("#divSmallBoxes").append(c), SmallBoxesAnchos = $("#smallbox" + SmallBoxes).height() + 40) : ($("#divSmallBoxes").append(c), $("#smallbox" + SmallBoxes).css("top", SmallBoxesAnchos), SmallBoxesAnchos = SmallBoxesAnchos + $("#smallbox" + SmallBoxes).height() + 20, $(".SmallBox").each(function(a) {
			0 == a ? ($(this).css("top", 20), heightPrev = $(this).height() + 40, SmallBoxesAnchos = $(this).height() + 40) : ($(this).css("top", heightPrev), heightPrev = heightPrev + $(this).height() + 20, SmallBoxesAnchos = SmallBoxesAnchos + $(this).height() + 20)
		}))
	}
	var h = $("#smallbox" + SmallBoxes);
	void 0 == a.color ? h.css("background-color", "#004d60") : h.css("background-color", a.color);
	var i;
	void 0 != a.colors && a.colors.length > 0 && (h.attr("colorcount", "0"), i = setInterval(function() {
		var b = h.attr("colorcount");
		h.animate({
			backgroundColor: a.colors[b].color
		}), b < a.colors.length - 1 ? h.attr("colorcount", 1 * b + 1) : h.attr("colorcount", 0)
	}, a.colortime)), void 0 != a.timeout && setTimeout(function() {
		clearInterval(i); {
			var a = $(this).height() + 20;
			$("#" + f).css("top")
		}
		0 != $("#" + f + ":hover").length ? $("#" + f).on("mouseleave", function() {
			SmallBoxesAnchos -= a, $("#" + f).remove(), "function" == typeof b && b && b();
			var c = 0;
			$(".SmallBox").each(function(a) {
				0 == a ? ($(this).animate({
					top: 20
				}, 300), c = $(this).height() + 40, SmallBoxesAnchos = $(this).height() + 40) : ($(this).animate({
					top: c
				}, 350), c = c + $(this).height() + 20, SmallBoxesAnchos = SmallBoxesAnchos + $(this).height() + 20)
			})
		}) : (clearInterval(i), SmallBoxesAnchos -= a, "function" == typeof b && b && b(), $("#" + f).removeClass().addClass("SmallBox").animate({
			opacity: 0
		}, 300, function() {
			$(this).remove();
			var
			a = 0;
			$(".SmallBox").each(function(b) {
				0 == b ? ($(this).animate({
					top: 20
				}, 300), a = $(this).height() + 40, SmallBoxesAnchos = $(this).height() + 40) : ($(this).animate({
					top: a
				}), a = a + $(this).height() + 20, SmallBoxesAnchos = SmallBoxesAnchos + $(this).height() + 20)
			})
		}))
	}, a.timeout), $("#smallbox" + SmallBoxes).bind("click", function() {
		clearInterval(i), "function" == typeof b && b && b(); {
			var a = $(this).height() + 20;
			$(this).attr("id"), $(this).css("top")
		}
		SmallBoxesAnchos -= a, $(this).removeClass().addClass("SmallBox").animate({
			opacity: 0
		}, 300, function() {
			$(this).remove();
			var a = 0;
			$(".SmallBox").each(function(b) {
				0 == b ? ($(this).animate({
					top: 20
				}, 300), a = $(this).height() + 40, SmallBoxesAnchos = $(this).height() + 40) : ($(this).animate({
					top: a
				}, 350), a = a + $(this).height() + 20, SmallBoxesAnchos = SmallBoxesAnchos + $(this).height() + 20)
			})
		})
	})
};