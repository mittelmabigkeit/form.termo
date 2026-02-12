setTimeout(
    function () {
        playerName = new CircleType(document.getElementById('playerName')).radius(384);
    },
    1000
);

function changeFont() {
    /*
    let nameDiv = document.querySelector(".choose__slider__item__name");
    let nameContent = nameDiv.querySelector("div");
    let spanS = nameContent.getElementsByTagName("span");
    let spanWidth = 0;

    for (var i = 0; i < spanS.length; ++i) {
        var item = spanS[i];
        item.innerHTML = 'this is value';
    }


    let width = nameDiv.offsetWidth;
    let fontSizeOrigin = parseInt(window.getComputedStyle(nameContent).getPropertyValue('font-size'));
    fontSize = fontSizeOrigin;
    if (nameContent.offsetWidth > width) {
        while (nameContent.offsetWidth > width) {
            fontSize--;
            nameDiv.style.fontSize = fontSize + "px";
        }
    } else {
        while (nameContent.offsetWidth < width && fontSize <= 41) {
            fontSize++;
            nameDiv.style.fontSize = fontSize + "px";
        }
    }
    */
    playerName = new CircleType(document.getElementById('playerName'))
        .radius(384);
}

$(document).ready(function () {
    initJS();
});

BX.addCustomEvent('onAjaxSuccess', function () {
    initJS();
    changeFont();
});

function initJS() {
    $(".js-player-name").on("click", function () {
        $("input[name='type-termo']").prop("value", "player-name");
        $("input[name='current-tab']").prop("value", "player");
        BX.fireEvent(
            BX('sendForm'),
            "click"
        );
    })

    $(".js-my-name").on("click", function () {
        $("input[name='type-termo']").prop("value", "my-name");
        $("input[name='current-tab']").prop("value", "my");
        BX.fireEvent(
            BX('sendForm'),
            "click"
        );
    });

    $('#choiseProd').change(function () {
        const inputName = $(this).closest(".choose-select").data("name-input");
        if (inputName == "type-form") {
            var select = $('#choiseProd option:selected');
            var name = select.data("name");
            var id = select.data("id");
            $("input[name='type-form']").prop("value", name);
            $("input[name='type-form-id']").prop("value", id);
        }
        BX.fireEvent(
            BX('sendForm'),
            "click"
        );
    });
    $('#color').change(function () {
        const inputName = $(this).closest(".ml-auto").data("name-input");
        if (inputName == "color") {
            var select = $('#color option:selected');
            var name = select.data("name");
            $("input[name='color']").prop("value", name);
        }
        BX.fireEvent(
            BX('sendForm'),
            "click"
        );
    });
    $('.select-options li').on('click', function (e) {
        const inputName = $(this).closest(".termo__player-name").data("name-input");
        if (inputName == "player-name") {
            $(this).closest(".termo__player-name").find("div div div").text('#' + $(this).data("num") + ' ' + $(this).data("name"));
            $("input[name='player-number']").prop("value", $(this).data("num"));
            $("input[name='player-name']").prop("value", $(this).data("name"));
            $(".t-shirt__number").text($(this).data("num"));
            $(".t-shirt__name").text($(this).data("name"));
        }
        BX.fireEvent(
            BX('sendForm'),
            "click"
        );
    });

    $('input[type=text][name="my-name"]').change(function () {
        $(".choose__slider__item__name").text($(this).val());
        BX.fireEvent(
            BX('sendForm'),
            "click"
        );
    });
    $('input[type=text][name="my-number"]').change(function () {
        $(".choose__slider__item__num").text($(this).val());
        $('input[type=hidden][name="count-num"]').prop("value", $(this).val().length);
        BX.fireEvent(
            BX('sendForm'),
            "click"
        );
    });

    $('.checkbox-sponsor').change(function () {
        if ($('input[type=checkbox][name="player-checkobox"]').is(':checked')) {
            $('input[type=checkbox][name="player-checkobox"]').prop("value", $("#sponsor_value").data("value"));
            $('input[type=hidden][name="show-sponsor"]').prop("value", "Y");
        } else {
            $('input[type=checkbox][name="player-checkobox"]').prop("value", "");
            $('input[type=hidden][name="show-sponsor"]').prop("value", "N");
        }
        BX.fireEvent(
            BX('sendForm'),
            "click"
        );
    });

    $('input[type=radio][name="size"]').change(function () {
        BX.fireEvent(
            BX('sendForm'),
            "click"
        );
    });

    $("#remove-name").on("click", function () {
        $("input[name='show-my-name']").prop("value", "N");
        $("input[name='my-name']").prop("value", "");
        BX.fireEvent(
            BX('sendForm'),
            "click"
        );
    })
    $("#remove-number").on("click", function () {
        $("input[name='show-my-number']").prop("value", "N");
        $("input[name='my-number']").prop("value", "");
        $('input[type=hidden][name="count-num"]').prop("value", "0");
        BX.fireEvent(
            BX('sendForm'),
            "click"
        );
    })
    $("#add-name").on("click", function () {
        $("input[name='show-my-name']").prop("value", "Y");
        BX.fireEvent(
            BX('sendForm'),
            "click"
        );
    })
    $("#add-number").on("click", function () {
        $("input[name='show-my-number']").prop("value", "Y");
        BX.fireEvent(
            BX('sendForm'),
            "click"
        );
    })

    $('[name="buy"]').on("click", function () {
        $('[name="get-buy"]').val("Y");
        BX.fireEvent(
            BX('sendForm'),
            "click"
        );
    });
}
