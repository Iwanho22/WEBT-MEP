Vue.createApp({
    data() {
        return {
            rows: 6,
            cols: 4,
            markedHolds: [],
            routeName: '',
            routeGrade: '',
            climbed: false,
            loggedRoutes: []
        }
    },
    methods: {
        drawCruxBoard() {
            const canvas = document.getElementById('crux-board');
            const ctx = canvas.getContext('2d');
            const cellWidth = canvas.width / this.cols;
            const cellHeight = canvas.height / this.rows;

            const holdTypes = [['circle', 'triangle', 'double-line', 'circle'],
            ['rectangle', 'line', 'rectangle', 'circle'],
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
            this.markedHolds = Array.from({ length: this.rows}, () => 
                Array.from({ length: this.cols}, () => false)
            );
            for (let row = 0; row < this.rows; row++){
                // one or tow holds
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
            console.log(e);

            const climb = {
                name: this.routeName,
                grade: this.routeGrade,
                climbed: this.climbed,
                route: this.markedHolds
            }

            const xhr = new XMLHttpRequest();
            xhr.onload = function () {
                console.log("loaded")
            }
            xhr.open("POST", "climb", true);
            xhr.send(JSON.stringify(climb));
        },
        getRoutes() {
            const xhr = new XMLHttpRequest();
            xhr.onload =  () => {
                this.loggedRoutes = JSON.parse(xhr.response);
                console.log(this.loggedRoutes);
            }
            xhr.open("GET", "climb", true);
            xhr.send();
        }
    },
    mounted: function () {
        this.drawCruxBoard();
        this.getRoutes();
    }
}).mount('#content')