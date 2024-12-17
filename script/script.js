Vue.createApp({
    data() {
        return {
            rows: 6,
            cols: 4,
            id: 0,
            markedHolds: [],
            routeName: '',
            routeGrade: '',
            climbed: false,
            loggedRoutes: [],
            errorMsg: [],
            scale: 'fb_bloc'
        }
    },
    methods: {
        drawCruxBoard() {
            const canvas = document.getElementById('crux-board');
            const ctx = canvas.getContext('2d');
            const cellWidth = canvas.width / this.cols;
            const cellHeight = canvas.height / this.rows;
            const holdTypes = [['circle', 'triangle', 'double-line', 'circle'], ['rectangle', 'line', 'rectangle', 'circle'],
            ['line', 'triangle', 'line', 'double-line'],
            ['triangle', 'rectangle', 'rectangle', 'circle'],
            ['circle', 'line', 'line', 'circle'],
            ['double-line', 'triangle', 'double-line', 'double-line']]

            for (let row = 0; row < this.rows; row++) {
                for (let col = 0; col < this.cols; col++) {
                    const x = col * cellWidth + cellWidth / 2;
                    const y = row * cellHeight + cellHeight / 2;
                    const type = holdTypes[row][col];
                    const marked = this.markedHolds.length != 0 && this.markedHolds[row][col];
                    this.drawHold(type, x, y, ctx, marked);
                }
            }
        },
        drawHold(type, x, y, ctx, marked) {
            ctx.save();
            ctx.translate(x, y);
            ctx.strokeStyle = marked ? 'red' : 'black';
            ctx.lineWidth = 2;

            switch (type) {
                case 'circle': // Sloper
                    ctx.beginPath();
                    ctx.arc(0, 0, 10, 0, Math.PI * 2);
                    ctx.stroke();
                    break;
                case 'rectangle': // Jug
                    ctx.strokeRect(-15, -8, 30, 16);
                    break;
                case 'line': // Crimp
                    ctx.beginPath();
                    ctx.moveTo(-15, 0);
                    ctx.lineTo(15, 0);
                    ctx.stroke();
                    break;
                case 'double-line': // Pinch
                    ctx.beginPath();
                    ctx.moveTo(-12, -4);
                    ctx.lineTo(12, -4);
                    ctx.moveTo(-12, 4);
                    ctx.lineTo(12, 4);
                    ctx.stroke();
                    break;
                case 'triangle': // Pocket
                    ctx.beginPath();
                    ctx.moveTo(0, -12); // Top vertex
                    ctx.lineTo(-10, 6); // Bottom-left vertex
                    ctx.lineTo(10, 6); // Bottom-right vertex
                    ctx.closePath();
                    ctx.stroke();
                    break;
            }
            ctx.restore();
        },
        generateRoute() {
            this.markedHolds = Array.from({ length: this.rows }, () =>
                Array.from({ length: this.cols }, () => false)
            );
            for (let row = 0; row < this.rows; row++) {
                const twoHolds = Math.floor(Math.random() * 11) > 7;
                this.markedHolds[row][Math.floor(Math.random() * this.cols)] = true;

                if (twoHolds) {
                    let secondHold = 0;
                    do {
                        secondHold = Math.floor(Math.random() * this.cols)
                    } while (this.markedHolds[row][secondHold]);
                    this.markedHolds[row][secondHold] = true;
                }
            }
            this.drawCruxBoard();
        },
        logRoute(e) {
            e.preventDefault();
            this.validateGrade();
            this.validateRouteName();
            if (Object.keys(this.errorMsg).length === 0) {
                const climb = {
                    id: this.id,
                    name: this.routeName,
                    grade: this.routeGrade,
                    climbed: this.climbed || false,
                    route: this.markedHolds
                }

                this.sendRequest(this.id != 0 ? 'PATCH' : 'POST', 'climb' + (this.id != 0 ? `?id=${this.id}` : ''), () => {
                    this.getRoutes();
                }, JSON.stringify(climb));
                this.fillClimb(null);
            }
        },
        getRoutes() {
            const self = this;
            this.sendRequest('GET', 'climb', function () {
                self.loggedRoutes = JSON.parse(this.response);
            })
        },
        editRoute(id) {
            const self = this;
            this.sendRequest('GET', `climb?id=${id}`, function () {
                let route = JSON.parse(this.response);
                self.errorMsg = [];
                self.fillClimb(route);
            });
        },
        deleteRoute(id) {
            this.sendRequest('DELETE', `climb?id=${id}`, () => {
                this.getRoutes();
            })
        },
        sendRequest(method, url, callback, body = null) {
            const xhr = new XMLHttpRequest();
            xhr.onload = callback;
            xhr.open(method, url, true);
            xhr.send(body);
        },
        fillClimb(climb) {
            this.routeName = climb?.name || '';
            this.routeGrade = climb?.grade || '';
            this.climbed = climb?.climbed || false;
            this.markedHolds = climb?.route || [];
            this.id = climb?.id || 0;
            this.drawCruxBoard();
        },
        validateRouteName() {
            if (this.routeName.length < 5) {
                this.errorMsg['routeName'] = "Der Routenname muss mindestens 5 Zeichen lang sein.";
            } else {
                delete this.errorMsg['routeName'];
            }
        },
        validateGrade() {
            // todo fix
            if (this.routeGrade.length < 1) {
                this.errorMsg['grade'] = "Wählen sie einen gültigen schwierigkeitsgrad aus der Liste aus.";
            } else {
                delete this.errorMsg['grade'];
            }
        },
        scaleChanged() {
            document.cookie = `scale=${this.scale}`; 
            this.getRoutes();
        }
    },
    mounted: function () {
        this.drawCruxBoard();
        this.getRoutes();
        this.scale = document.cookie.split("; ").find((row) => row.startsWith("scale="))?.split("=")[1] || 'fb_bloc';
    }
}).mount('#content')