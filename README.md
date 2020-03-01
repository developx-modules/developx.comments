# developx.comments

Developx: модуль комментариев для 1С-Битрикс
с использованием инфоблоков и ГуглКаптчи 3

Для работы ГуглКаптчи требуется модуль https://github.com/developx-modules/developx.gcaptcha

Установка модуля:

1) Скачать модуль из https://marketplace.1c-bitrix.ru/solutions/developx.comments и установить.

2) Добавить новый инфоблок в админке, который будет использоваться для сохранения комментариев.

3) Добавить компонент developx.comments на нужную страницу
Инструкция https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=34&LESSON_ID=9163&LESSON_PATH=3905.4457.6945.9163

4) Для подключения ГуглКаптчи в настройках модуля должна быть отмечена опция "Подключить ГуглКаптчу". 
А также установлен, настроен и активирован модуль ГуглКаптчи(developx.gcaptcha)

5) Модуль автоматически создает тип почтового события (DEVELOPX_NEW_COMMENT) и почтовый шаблон.
Отредактировать шаблон можно в настройках почтовых шаблонов /bitrix/admin/message_admin.php?lang=ru